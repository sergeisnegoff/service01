<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Docrobot;

use App\Helper\PropelHelper;
use App\Model\CompanyOrganizationShopQuery;
use App\Model\DocrobotSetting;
use App\Model\Invoice;
use App\Model\Product;
use App\Service\Invoice\InvoiceService;
use App\Service\Product\ProductService;
use App\Service\Supplier\SupplierService;
use App\Service\Unit\UnitService;
use Exception;
use LogicException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;

class DocrobotImportService
{
    const CATEGORY_TITLE = 'Docrobot';

    protected ?DocrobotClient $client = null;
    private DocrobotSetting $setting;
    private InvoiceService $invoiceService;
    private ProductService $productService;
    private UnitService $unitService;
    private SupplierService $supplierService;

    public function __construct(
        InvoiceService $invoiceService,
        ProductService $productService,
        UnitService $unitService,
        SupplierService $supplierService
    ) {
        $this->invoiceService = $invoiceService;
        $this->productService = $productService;
        $this->unitService = $unitService;
        $this->supplierService = $supplierService;
    }

    public function init(DocrobotSetting $setting)
    {
        $this->setting = $setting;
    }

    protected function getClient(): DocrobotClient
    {
        if (!$this->client) {
            $this->client = new DocrobotClient($this->setting->getLogin(), $this->setting->getPassword());
        }

        return $this->client;
    }

    public function processImportDocuments(): void
    {
        if (!$this->setting) {
            throw new LogicException('Method init not called');
        }

        $connection = Propel::getConnection();
        $company = $this->setting->getCompany();

        $client = $this->getClient();
        $documents = $client->getEdiDocs();

        $invoices = $this->invoiceService->getInvoicesFromImport($company);
        $shops = $company->getCompanyOrganizationShops(CompanyOrganizationShopQuery::makeDocrobotQuery())->toKeyIndex('DocrobotExternalCode');

        if (!$shops) {
            return;
        }

        $i = 1;

        foreach ($documents as $document) {
            $id = (int) $document['intDocID'];

            $documentData = $client->getEdiDocBody($id);
            $shop = $shops[$documentData['HEAD'][0]['DELIVERYPLACE']] ?? null;

            $existInvoice = $invoices[$id] ?? null;

            if ($existInvoice) {
                continue;
            }

            try {
                $invoice = $this->makeInvoice($document['intDocID'], $documentData);
                $invoice->setCompanyOrganizationShop($shop)->save();
                $this->importProducts($invoice, $documentData);

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
            }

            if ($i >= 200) {
                PropelHelper::commitTransaction($connection);
            }

            $i++;
        }
    }

    protected function makeInvoice(string $id, array $documentData): Invoice
    {
        $company = $this->setting->getCompany();
        $storeHouseSetting = $company->getStoreHouseSetting();
        $supplier = $this->supplierService->findSupplierByDocrobotCode($documentData['HEAD'][0]['SUPPLIER']);

        return $this->invoiceService->createFromArray([
            'buyerId' => $company->getId(),
            'externalCode' => $id,
            'warehouseId' => $storeHouseSetting ? $storeHouseSetting->getWarehouseId() : null,
            'supplierId' => $supplier ? $supplier->getId() : null,
            'createdAt' => $documentData['DATE'],
        ], TableMap::TYPE_CAMELNAME);
    }

    protected function importProducts(Invoice $invoice, array $documentData): void
    {
        $productCategory = $this->productService->getProductCategoryFromDocrobot();
        $supplier = $invoice->getCompanyRelatedBySupplierId();

        $existProducts = $this->productService->getProductFromEdo();
        $products = $documentData['HEAD'][0]['PACKINGSEQUENCE'][0]['POSITION'];

        foreach ($products as $product) {
            if (!$unit = $this->unitService->findUnit($product['DELIVEREDUNIT'])) {
                $unit = $this->unitService->create($product['DELIVEREDUNIT']);
            }

            if (!$newProduct = $existProducts[$product['PRODUCT']] ?? null) {
                $newProduct = new Product();
            }

            $this->productService->fillFromArray(
                $newProduct,
                [
                    'unitId' => $unit->getId(),
                    'companyId' => $supplier ? $supplier->getId() : null,
                    'categoryId' => $productCategory->getId(),
                    'nomenclature' => $product['DESCRIPTION'],
                    'price' => $product['PRICE'],
                    'article' => $product['PRODUCT'],
                    'vat' => $product['TAXRATE'],
                    'edo' => true,
                ],
                TableMap::TYPE_CAMELNAME
            );
            $newProduct->save();

            $this->invoiceService->createInvoiceProductFromArray(
                [
                    'invoiceId' => $invoice->getId(),
                    'productId' => $newProduct->getId(),
                    'unitId' => $unit->getId(),
                    'price' => $newProduct->getPrice(),
                    'quantity' => $product['DELIVEREDQUANTITY'],
                ],
                TableMap::TYPE_CAMELNAME
            );
        }
    }
}
