<?php

declare(strict_types=1);

namespace App\Service\Iiko;

use App\Helper\PropelHelper;
use App\Model\Counterparty;
use App\Model\IikoSetting;
use App\Model\Product;
use App\Model\Unit;
use App\Model\Warehouse;
use App\Service\Counterparty\CounterpartyService;
use App\Service\Product\ProductService;
use App\Service\Supplier\SupplierService;
use App\Service\Unit\UnitService;
use App\Service\Warehouse\WarehouseService;
use Exception;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;

class IikoImportService
{
    const CATEGORY_TITLE = 'Iiko';

    private IikoClient $client;
    private WarehouseService $warehouseService;
    private CounterpartyService $counterpartyService;
    private SupplierService $supplierService;
    private ProductService $productService;
    private UnitService $unitService;

    public function __construct(
        IikoClient $client,
        WarehouseService $warehouseService,
        CounterpartyService $counterpartyService,
        SupplierService $supplierService,
        ProductService $productService,
        UnitService $unitService
    ) {
        $this->client = $client;
        $this->warehouseService = $warehouseService;
        $this->counterpartyService = $counterpartyService;
        $this->supplierService = $supplierService;
        $this->productService = $productService;
        $this->unitService = $unitService;
    }

    public function processFullImport(IikoSetting $setting): void
    {
        $this->importWarehouses($setting);
        $this->importCategories($setting);
        $this->importUnits($setting);
        $this->importProducts($setting);
        $this->importCounterparties($setting);
    }

    public function importWarehouses(IikoSetting $setting): void
    {
        $iikoClient = $this->client;
        $iikoClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $warehouses = $iikoClient->getWarehouses();
        $existWarehouses = $this->warehouseService->getWarehousesFromIikoImport($company);

        $map = [
            'id' => 'externalCode',
            'name' => 'title'
        ];

        $i = 1;

        foreach ($warehouses as $warehouse) {
            PropelHelper::startTransaction($connection);

            try {
                $values = $warehouse['value'];
                $fields = ['companyId' => $company->getId()];

                foreach ($values as $value) {
                    $mapField = $map[$this->normalizeName($value['name'])] ?? null;

                    if (!$mapField) {
                        continue;
                    }

                    $fields[$mapField] = $value['value'];
                }

                if (!isset($fields['externalCode'])) {
                    continue;
                }

                $existWarehouse = $existWarehouses[$fields['externalCode']] ?? null;

                if (!$existWarehouse) {
                    $newWarehouse = new Warehouse();
                    $this->warehouseService->fillFromArray($newWarehouse, $fields, TableMap::TYPE_CAMELNAME);
                    $newWarehouse->save();

                } else {
                    $this->warehouseService->fillFromArray($existWarehouse, $fields, TableMap::TYPE_CAMELNAME);
                    $existWarehouse->save();
                }

                if ($i >= 200) {
                    PropelHelper::commitTransaction($connection);
                }

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
                $iikoClient->logout();
            }

            $i++;
        }

        PropelHelper::commitTransaction($connection);
        $iikoClient->logout();
    }

    public function importCategories(IikoSetting $setting): void
    {
        $iikoClient = $this->client;
        $iikoClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $categories = $iikoClient->getGroups();
        $existCategories = $this->supplierService->getProductCategoriesFromIikoImport($company);

        $i = 1;

        foreach ($categories as $category) {
            PropelHelper::startTransaction($connection);

            try {
                $existCategory = $existCategories[$category['id']] ?? null;

                if (!$existCategory) {
                    $this->supplierService->createProductCategory($category['name'], $company, $category['id']);

                } else {
                    $this->supplierService->editProductCategory($existCategory, $category['name'], $category['id']);
                }

                if ($i >= 200) {
                    PropelHelper::commitTransaction($connection);
                }

                $i++;

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
                $iikoClient->logout();
            }
        }

        PropelHelper::commitTransaction($connection);
        $iikoClient->logout();
    }

    public function importUnits(IikoSetting $setting): void
    {
        $iikoClient = $this->client;
        $iikoClient->init($setting);
        $connection = Propel::getConnection();

        $units = $iikoClient->getUnits();
        $existUnits = $this->unitService->getUnitsFromIikoImport();
        $company = $setting->getCompany();

        $i = 1;

        foreach ($units as $unit) {
            PropelHelper::startTransaction($connection);

            try {
                $existCategory = $existUnits[$unit['id']] ?? null;

                if (!$existCategory) {
                    $newUnit = new Unit();
                    $newUnit
                        ->setCompany($company)
                        ->setTitle($unit['name'])
                        ->setExternalCode($unit['id'])
                        ->setFromIiko(true)
                        ->save();
                }

                if ($i >= 200) {
                    PropelHelper::commitTransaction($connection);
                }

                $i++;

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
                $iikoClient->logout();
            }
        }

        PropelHelper::commitTransaction($connection);
        $iikoClient->logout();
    }

    protected function filterProducts(array $products): array
    {
        return array_filter($products, fn ($product) => $product['type'] === 'GOODS');
    }

    public function importProducts(IikoSetting $setting): void
    {
        $iikoClient = $this->client;
        $iikoClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $products = $this->filterProducts($iikoClient->getProducts());

        $existCategories = $this->supplierService->getProductCategoriesFromIikoImport($company);
        $existUnits = $this->unitService->getUnitsFromIikoImport();
        $existProducts = $this->productService->getProductsFromIikoImport($company);
        $fallbackCategory = $this->productService->getProductCategoryFromIiko($company);

        $i = 1;

        foreach ($products as $product) {
            PropelHelper::startTransaction($connection);

            try {
                $existUnit = $existUnits[$product['mainUnit']] ?? null;
                $existCategory = $existCategories[$product['category'] ?? $product['parent']] ?? $fallbackCategory;

                if (!$existCategory || !$existUnit) {
                    continue;
                }

                $existProduct = $existProducts[$product['id']] ?? null;

                $data = [
                    'companyId' => $company->getId(),
                    'unitId' => $existUnit->getId(),
                    'categoryId' => $existCategory->getId(),
                    'nomenclature' => $product['name'],
                    'externalCode' => $product['id'],
                    'price' => $product['defaultSalePrice'],
                ];

                if (!$existProduct) {
                    $newProduct = new Product();
                    $this->productService->fillFromArray($newProduct, $data, TableMap::TYPE_CAMELNAME);
                    $newProduct->save();

                } else {
                    $this->productService->fillFromArray($existProduct, $data, TableMap::TYPE_CAMELNAME);
                    $existProduct->save();
                }

                if ($i >= 200) {
                    PropelHelper::commitTransaction($connection);
                }

                $i++;

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
                $iikoClient->logout();
            }
        }

        PropelHelper::commitTransaction($connection);
        $iikoClient->logout();
    }

    public function importCounterparties(IikoSetting $setting): void
    {
        $iikoClient = $this->client;
        $iikoClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $counterparties = $iikoClient->getCounterparties();
        $existCounterparties = $this->counterpartyService->getCounterpartiesFromIikoImport($company);

        $map = [
            'id' => 'externalCode',
            'name' => 'title'
        ];

        $i = 1;

        foreach ($counterparties as $counterparty) {
            PropelHelper::startTransaction($connection);

            try {
                $values = $counterparty['value'];
                $fields = ['companyId' => $company->getId()];

                foreach ($values as $value) {
                    $mapField = $map[$this->normalizeName($value['name'])] ?? null;

                    if (!$mapField) {
                        continue;
                    }

                    $fields[$mapField] = $value['value'];
                }

                if (!isset($fields['externalCode'])) {
                    continue;
                }

                $existCounterparty = $existCounterparties[$fields['externalCode']] ?? null;

                if (!$existCounterparty) {
                    $newCounterparty = new Counterparty();
                    $this->counterpartyService->fillFromArray($newCounterparty, $fields, TableMap::TYPE_CAMELNAME);
                    $newCounterparty->save();

                } else {
                    $this->counterpartyService->fillFromArray($existCounterparty, $fields, TableMap::TYPE_CAMELNAME);
                    $existCounterparty->save();
                }

                if ($i >= 200) {
                    PropelHelper::commitTransaction($connection);
                }

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
                $iikoClient->logout();
            }

            $i++;
        }

        PropelHelper::commitTransaction($connection);
        $iikoClient->logout();
    }

    protected function normalizeName(string $name): string
    {
        return preg_replace('/{}/', '', $name);
    }
}
