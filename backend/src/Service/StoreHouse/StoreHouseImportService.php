<?php

declare(strict_types=1);

namespace App\Service\StoreHouse;

use App\Helper\ImportHelper;
use App\Helper\PropelHelper;
use App\Model\Counterparty;
use App\Model\Product;
use App\Model\StoreHouseSetting;
use App\Model\Unit;
use App\Model\Warehouse;
use App\Service\Counterparty\CounterpartyService;
use App\Service\Product\ProductService;
use App\Service\StoreHouse\Messenger\StoreHouseImportMessage;
use App\Service\Supplier\SupplierService;
use App\Service\Unit\UnitService;
use App\Service\Warehouse\WarehouseService;
use Exception;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use Symfony\Component\Messenger\MessageBusInterface;

class StoreHouseImportService
{
    public const WAREHOUSE_TYPES = [1, 7];
    public const CATEGORY_TITLE = 'StoreHouse';

    private StoreHouseClient $storeHouseClient;
    private WarehouseService $warehouseService;
    private UnitService $unitService;
    private SupplierService $supplierService;
    private ProductService $productService;
    private CounterpartyService $counterpartyService;
    private MessageBusInterface $messageBus;

    public function __construct(
        StoreHouseClient $storeHouseClient,
        WarehouseService $warehouseService,
        UnitService $unitService,
        SupplierService $supplierService,
        ProductService $productService,
        CounterpartyService $counterpartyService,
        MessageBusInterface $messageBus
    ) {
        $this->storeHouseClient = $storeHouseClient;
        $this->warehouseService = $warehouseService;
        $this->unitService = $unitService;
        $this->supplierService = $supplierService;
        $this->productService = $productService;
        $this->counterpartyService = $counterpartyService;
        $this->messageBus = $messageBus;
    }

    public function sendFullImportToQueue(StoreHouseSetting $setting)
    {
        $this->messageBus->dispatch((new StoreHouseImportMessage($setting)));
    }

    public function processFullImport(StoreHouseSetting $setting)
    {
        ImportHelper::initImportOptions();
        $this->storeHouseClient->init($setting);

        $this->importWarehouses($setting);
        $this->importCategories($setting);
        $this->importUnits($setting);
        $this->importProducts($setting);
        $this->importCounterparties($setting);
    }

    public function importWarehouses(StoreHouseSetting $setting)
    {
        $this->storeHouseClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $warehouses = $this->normalizeData(AbstractStoreHouseClient::TABLE_WAREHOUSE, $this->storeHouseClient->getWarehouses());
        $existWarehouses = $this->warehouseService->getWarehousesFromIikoImport($company);

        $defaultWarehouse = null;

        $i = 1;

        foreach ($warehouses as $warehouse) {
            PropelHelper::startTransaction($connection);

            if (!in_array($warehouse['TypeMask'], self::WAREHOUSE_TYPES)) {
                continue;
            }

            $existWarehouse = $existWarehouses[$warehouse['Rid']] ?? null;

            $fields = [
                'externalCode' => $warehouse['Rid'],
                'title' => $warehouse['Name'],
                'companyId' => $company->getId(),
                'fromStoreHouse' => true,
            ];

            if (!$existWarehouse) {
                $existWarehouse = new Warehouse();
            }

            $this->warehouseService->fillFromArray($existWarehouse, $fields, TableMap::TYPE_CAMELNAME);
            $existWarehouse->save();

            if (!$defaultWarehouse) {
                $defaultWarehouse = $existWarehouse;
            }

            if ($i >= PropelHelper::MAX_STATEMENT_COUNT) {
                PropelHelper::commitTransaction($connection);
            }

            $i++;
        }

        if ($defaultWarehouse && !$setting->getWarehouseId()) {
            $setting->setWarehouse($defaultWarehouse)->save();
        }

        PropelHelper::commitTransaction($connection);
    }

    public function importUnits(StoreHouseSetting $setting)
    {
        $this->storeHouseClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $units = $this->normalizeData(AbstractStoreHouseClient::TABLE_UNIT, $this->storeHouseClient->getUnits());
        $existUnits = $this->unitService->getUnitsFromStoreHouseImport($company);

        $i = 1;

        foreach ($units as $unit) {
            PropelHelper::startTransaction($connection);

            $existCategory = $existUnits[$unit['Rid']] ?? null;

            if (!$existCategory) {
                $newUnit = new Unit();
                $newUnit
                    ->setCompany($company)
                    ->setTitle($unit['Base name'])
                    ->setExternalCode($unit['Rid'])
                    ->setFromStoreHouse(true)
                    ->save();
            }

            if ($i >= PropelHelper::MAX_STATEMENT_COUNT) {
                PropelHelper::commitTransaction($connection);
            }

            $i++;
        }

        PropelHelper::commitTransaction($connection);
    }

    public function importCategories(StoreHouseSetting $setting): void
    {
        $this->storeHouseClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $categories = $this->normalizeData(AbstractStoreHouseClient::TABLE_CATEGORIES, $this->storeHouseClient->getProductCategories());
        $existCategories = $this->supplierService->getProductCategoriesFromIikoImport($company);

        $i = 1;

        foreach ($categories as $category) {
            PropelHelper::startTransaction($connection);

            try {
                $categoryId = $this->normalizeGuid($category[4]);
                $existCategory = $existCategories[$categoryId] ?? null;

                if (!$existCategory) {
                    $this->supplierService->createProductCategory($category[3], $company, $categoryId);

                } else {
                    $this->supplierService->editProductCategory($existCategory, $category[3], $categoryId);
                }

                if ($i >= PropelHelper::MAX_STATEMENT_COUNT) {
                    PropelHelper::commitTransaction($connection);
                }

                $i++;

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
            }
        }

        PropelHelper::commitTransaction($connection);
    }

    public function importProducts(StoreHouseSetting $setting)
    {
        $this->storeHouseClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $existCategories = $this->supplierService->getProductCategoriesFromStoreHouseImport($company);
        $existUnits = $this->unitService->getUnitsFromStoreHouseImport($company);
        $existProducts = $this->productService->getProductsFromIikoImport($company);
        $fallbackCategory = $this->productService->getProductCategoryFromStoreHouse($company);

        $productGroups = $this->normalizeData(AbstractStoreHouseClient::TABLE_PRODUCT_GROUPS, $this->storeHouseClient->getProductGroups());

        $i = 1;

        foreach ($productGroups as $productGroup) {
            $products = $this->normalizeData(AbstractStoreHouseClient::TABLE_PRODUCTS, $this->storeHouseClient->getProducts($productGroup['Rid']));

            foreach ($products as $product) {
                PropelHelper::startTransaction($connection);

                $existUnit = $existUnits[$product['Ei: Name']] ?? null;
                $existCategory = $existCategories[$product['200\3']] ?? $fallbackCategory;

                $existProduct = $existProducts[$product['Rid']] ?? null;

                $data = [
                    'companyId' => $company->getId(),
                    'unitId' => $existUnit ? $existUnit->getId() : null,
                    'categoryId' => $existCategory->getId(),
                    'nomenclature' => $product['210\3'],
                    'externalCode' => $product['Rid'],
                    'price' => $product['Price1'],
                    'options' => json_encode($product),
                ];

                if (!$existProduct) {
                    $existProduct = new Product();
                }

                $existProduct->fromArray($data, TableMap::TYPE_CAMELNAME);
                $existProduct->save();

                if ($i >= PropelHelper::MAX_STATEMENT_COUNT) {
                    PropelHelper::commitTransaction($connection);
                }

                $i++;
            }
        }

        PropelHelper::commitTransaction($connection);
    }

    public function importCounterparties(StoreHouseSetting $setting): void
    {
        $this->storeHouseClient->init($setting);
        $connection = Propel::getConnection();

        $company = $setting->getCompany();
        $counterparties = $this->normalizeData(AbstractStoreHouseClient::TABLE_COUNTERPARTIES, $this->storeHouseClient->getCounterparties());
        $existCounterparties = $this->counterpartyService->getCounterpartiesFromIikoImport($company);

        $i = 1;

        foreach ($counterparties as $counterparty) {
            PropelHelper::startTransaction($connection);

            try {
                $fields = [
                    'companyId' => $company->getId(),
                    'title' => $counterparty['Name'],
                    'externalCode' => $counterparty['Rid'],
                ];

                $existCounterparty = $existCounterparties[$fields['externalCode']] ?? null;

                if (!$existCounterparty) {
                    $existCounterparty = new Counterparty();
                }

                $this->counterpartyService->fillFromArray($existCounterparty, $fields, TableMap::TYPE_CAMELNAME);
                $existCounterparty->save();

                if ($i >= PropelHelper::MAX_STATEMENT_COUNT) {
                    PropelHelper::commitTransaction($connection);
                }

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
            }

            $i++;
        }

        PropelHelper::commitTransaction($connection);
    }


    protected function findTable(int $tableId, array $data): array
    {
        $data = array_filter($data, fn($item) => $item['head'] === (string) $tableId);
        return array_shift($data) ?? [];
    }

    public function normalizeData(int $tableId, array $data): array
    {
        $out = [];

        $table = $this->findTable($tableId, $data);
        $fields = $table['fields'] ?? [];

        if (!$fields) {
            return [];
        }

        foreach ($table['values'] as $fieldCode => $items) {
            foreach ($items as $key => $item) {
                if (!isset($out[$key])) {
                    $out[$key] = [$fields[$fieldCode] => $item];

                } else {
                    $out[$key][$fields[$fieldCode]] = $item;
                }
            }
        }

        return $out;
    }

    public function normalizeGuid(string $guid): string
    {
        return preg_replace('/{|}/', '', $guid);
    }
}
