<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Diadoc;

use AgentSIB\Diadoc\Api\Proto\Documents\Document;
use AgentSIB\Diadoc\Api\Proto\Documents\DocumentList;
use AgentSIB\Diadoc\Api\Proto\Events\Entity;
use AgentSIB\Diadoc\DiadocApi;
use AgentSIB\Diadoc\Filter\DocumentsFilter;
use App\Helper\ImportHelper;
use App\Helper\PropelHelper;
use App\Model\CompanyOrganizationShopQuery;
use App\Model\DiadocSetting;
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
use Sabre\Xml as Xml;

class DiadocImportService
{
    const CATEGORY_TITLE = 'Diadoc';

    private ?DiadocApi $diadocApi = null;
    private Xml\Service $xmlService;
    private DiadocSetting $diadocSetting;
    private ProductService $productService;
    private InvoiceService $invoiceService;
    private UnitService $unitService;
    private SupplierService $supplierService;

    public function __construct(
        ProductService $productService,
        InvoiceService $invoiceService,
        UnitService $unitService,
        SupplierService $supplierService
    ) {
        $this->xmlService = new Xml\Service();
        $this->productService = $productService;
        $this->invoiceService = $invoiceService;
        $this->unitService = $unitService;
        $this->supplierService = $supplierService;
    }

    public function init(DiadocSetting $diadocSetting)
    {
        $this->diadocSetting = $diadocSetting;
    }

    protected function getApi(): DiadocApi
    {
        if (!$this->diadocApi) {
            $this->diadocApi = new DiadocApi($this->diadocSetting->getApiKey(), new DiadocSigner());
            $this->diadocApi->authenticateLogin($this->diadocSetting->getLogin(), $this->diadocSetting->getPassword());
        }

        return $this->diadocApi;
    }

    public function processImportDocuments(): void
    {
        if (!$this->diadocSetting) {
            throw new LogicException('Method init not called');
        }

        ImportHelper::initImportOptions();
        $connection = Propel::getConnection();

        $company = $this->diadocSetting->getCompany();
        $diadocApi = $this->getApi();

        $shops = $company->getCompanyOrganizationShops(CompanyOrganizationShopQuery::makeDiadocQuery());

        foreach ($shops as $shop) {
            $boxId = $shop->getDiadocExternalCode();
            $documentFilter = DocumentsFilter::create()
                ->setFilterDocumentClass(DocumentsFilter::DOCUMENT_CLASS_INBOUND);

            /** @var DocumentList $importDocumentsData */
            $importDocumentsData = $diadocApi->getDocuments($boxId, $documentFilter);
            $importDocuments = $importDocumentsData->getDocumentsList() ?? [];
            $invoices = $this->invoiceService->getInvoicesFromImport($company);

            $i = 1;

            /** @var Document $document */
            foreach ($importDocuments as $document) {
                $message = $diadocApi->getMessage($boxId, $document->getMessageId(), $document->getEntityId());
                $entities = $message->getEntitiesList();

                $existInvoice = $invoices[$document->getMessageId()] ?? null;

                if ($existInvoice) {
                    continue;
                }

                PropelHelper::startTransaction($connection);

                /** @var Entity $entity */
                foreach ($entities as $entity) {
                    if (!preg_match('/^ON_NSCHFDOPPR/', (string) $entity->getFileName())) {
                        continue;
                    }

                    try {
                        $parseData = $this->xmlService->parse($entity->getContent()->getData()->getContents());

                        $invoice = $this->makeInvoice($document->getMessageId(), $parseData);
                        $invoice->setCompanyOrganizationShop($shop)->save();
                        $this->importProducts($invoice, $parseData);

                    } catch (Exception $exception) {
                        PropelHelper::rollBack($connection);
                    }
                }

                if ($i >= 200) {
                    PropelHelper::commitTransaction($connection);
                }

                $i++;
            }

            PropelHelper::commitTransaction($connection);
        }
    }

    protected function getDocument(array $documentData): ?array
    {
        $documentData = array_filter($documentData, fn($item) => $this->normalizeName($item['name']) === 'Документ');

        return array_shift($documentData);
    }

    protected function getDocumentAdditionalInfo(array $documentData): array
    {
        $info = array_filter($documentData, fn($item) => $this->normalizeName($item['name']) === 'СвУчДокОбор');

        return array_shift($info);
    }

    protected function getProducts(array $documentData): ?array
    {
        $document = $this->getDocument($documentData);
        $products = array_filter($document['value'], fn($item) => $this->normalizeName($item['name']) === 'ТаблСчФакт');
        $products = array_shift($products);

        return array_filter($products['value'], fn($item) => $this->normalizeName($item['name']) === 'СведТов');
    }

    protected function getProductAdditionInfo(array $product)
    {
        $products = array_filter($product['value'], fn($item) => $this->normalizeName($item['name']) === 'ДопСведТов');
        return array_shift($products);
    }

    protected function normalizeName(string $name): string
    {
        return preg_replace('/{}/', '', $name);
    }

    protected function makeInvoice(string $id, array $documentData): Invoice
    {
        $data = $this->getDocument($documentData);
        $info = $this->getDocumentAdditionalInfo($documentData);

        $company = $this->diadocSetting->getCompany();
        $storeHouseSetting = $company->getStoreHouseSetting();
        $supplier = $this->supplierService->findSupplierByDiadocCode($info['attributes']['ИдОтпр']);

        return $this->invoiceService->createFromArray([
            'buyerId' => $company->getId(),
            'warehouseId' => $storeHouseSetting ? $storeHouseSetting->getWarehouseId() : null,
            'externalCode' => $id,
            'supplierId' => $supplier ? $supplier->getId() : null,
            'createdAt' => $data['attributes']['ДатаИнфПр'],
        ], TableMap::TYPE_CAMELNAME);
    }

    protected function importProducts(Invoice $invoice, array $documentData): void
    {
        $productCategory = $this->productService->getProductCategoryFromDiadoc();
        $supplier = $invoice->getCompanyRelatedBySupplierId();

        $existProducts = $this->productService->getProductFromEdo();
        $products = $this->getProducts($documentData);

        foreach ($products as $product) {
            $attributes = $product['attributes'];
            $additionalInfo = $this->getProductAdditionInfo($product);

            if (!$unit = $this->unitService->findUnit($additionalInfo['attributes']['НаимЕдИзм'])) {
                $unit = $this->unitService->create($additionalInfo['attributes']['НаимЕдИзм']);
            }

            if (!$newProduct = $existProducts[$additionalInfo['attributes']['КодТов']] ?? null) {
                $newProduct = new Product();
            }

            $this->productService->fillFromArray(
                $newProduct,
                [
                    'unitId' => $unit->getId(),
                    'companyId' => $supplier ? $supplier->getId() : null,
                    'categoryId' => $productCategory->getId(),
                    'nomenclature' => $attributes['НаимТов'],
                    'price' => $attributes['ЦенаТов'],
                    'article' => $additionalInfo['attributes']['КодТов'],
                    'vat' => $attributes['НалСт'],
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
                    'quantity' => $attributes['КолТов'],
                ],
                TableMap::TYPE_CAMELNAME
            );
        }
    }
}
