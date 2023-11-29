<?php


namespace App\Service\Invoice;


use App\EventPublisher\EventPublisher;
use App\Helper\ImportHelper;
use App\Helper\PriceFormatHelper;
use App\Helper\PropelHelper;
use App\Model\Company;
use App\Model\Invoice;
use App\Model\InvoiceProduct;
use App\Model\InvoiceProductQuery;
use App\Model\InvoiceQuery;
use App\Model\InvoiceStatus;
use App\Model\InvoiceStatusQuery;
use App\Model\Map\InvoiceTableMap;
use App\Model\Notification;
use App\Model\ProductManufacturerQuery;
use App\Model\User;
use App\Service\Buyer\BuyerOrganizationService;
use App\Service\Company\CompanyService;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\Iiko\IikoInvoiceService;
use App\Service\Invoice\Exception\InvoiceExchangeException;
use App\Service\Invoice\InvoiceComparisonData\InvoiceComparisonProductData;
use App\Service\Invoice\InvoiceData\InvoiceData;
use App\Service\Invoice\InvoiceData\InvoiceProductData;
use App\Service\Invoice\InvoiceList\InvoiceListContext;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Notification\NotificationService;
use App\Service\Notification\NotificationUserData;
use App\Service\Product\ProductService;
use App\Service\StoreHouse\StoreHouseInvoiceService;
use Exception;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class InvoiceService
{
    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;
    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;
    private DataObjectBuilder $dataObjectBuilder;
    private InvoiceComparisonService $comparisonService;
    private UrlGeneratorInterface $urlGenerator;
    private IikoInvoiceService $iikoInvoiceService;
    private StoreHouseInvoiceService $storeHouseInvoiceService;
    private BuyerOrganizationService $organizationService;
    private CompanyService $companyService;
    private ProductService $productService;

    public function __construct(
        ListConfigurationService $listConfigurationService,
        NotificationService $notificationService,
        EventPublisher $eventPublisher,
        DataObjectBuilder $dataObjectBuilder,
        InvoiceComparisonService $comparisonService,
        UrlGeneratorInterface $urlGenerator,
        IikoInvoiceService $iikoInvoiceService,
        StoreHouseInvoiceService $storeHouseInvoiceService,
        BuyerOrganizationService $organizationService,
        CompanyService $companyService,
        ProductService $productService
    ) {
        $this->listConfigurationService = $listConfigurationService;
        $this->notificationService = $notificationService;
        $this->eventPublisher = $eventPublisher;
        $this->dataObjectBuilder = $dataObjectBuilder;
        $this->comparisonService = $comparisonService;
        $this->urlGenerator = $urlGenerator;
        $this->iikoInvoiceService = $iikoInvoiceService;
        $this->storeHouseInvoiceService = $storeHouseInvoiceService;
        $this->organizationService = $organizationService;
        $this->companyService = $companyService;
        $this->productService = $productService;
    }

    public function retrieveInvoice($id): ?Invoice
    {
        return InvoiceQuery::create()->findPk($id);
    }

    // TODO: Свят, посмотри, нужно ли логику с такой фильтрацией перенести в существующие методы retrieveInvoiceCompany и retrieveInvoiceByCode
    public function retrieveInvoiceCompanySupplierOrBuyerById(Company $company, $id): ?Invoice
    {
        return InvoiceQuery::create()
            ->filterByCompanyRelatedBySupplierId($company)
            ->_or()
            ->filterByCompanyRelatedByBuyerId($company)
            ->findPk($id);
    }

    public function retrieveInvoiceCompanySupplierOrBuyerByCode(Company $company, $code): ?Invoice
    {
        return InvoiceQuery::create()
            ->filterByCompanyRelatedBySupplierId($company)
            ->_or()
            ->filterByCompanyRelatedByBuyerId($company)
            ->findOneByExternalCode($code);
    }

    public function retrieveInvoiceCompany(Company $company, $id): ?Invoice
    {
        return InvoiceQuery::create()->filterByCompanyRelatedBySupplierId($company)->findPk($id);
    }

    public function retrieveInvoiceByCode(Company $company, $code): ?Invoice
    {
        return InvoiceQuery::create()->filterByCompanyRelatedBySupplierId($company)->findOneByExternalCode($code);
    }

    public function changeInvoices(Company $company, array $invoices): array
    {
        $result = [];

        foreach ($invoices as $invoice) {
            $id = $invoice['id'] ?? null;
            $code = $invoice['cod'] ?? null;
            $newCode = $invoice['newCod'] ?? null;

            if ($code) {
                $invoice = $this->retrieveInvoiceByCode($company, $code);

            } else {
                $invoice = $this->retrieveInvoiceCompany($company, $id);
            }

            if (!$invoice) {
                $result[] = [
                    'id' => $id,
                    'cod' => $code,
                    'message' => 'Накладная не найдена',
                ];
                continue;
            }

            $invoice->setExternalCode($newCode ?: $code)->save();

            $result[] = $invoice;
        }

        return $result;
    }

    public function createInvoices(Company $company, array $invoices): array
    {
        ImportHelper::initImportOptions();

        $result = [];

        foreach ($invoices as $invoice) {
            if ($error = $this->validateInvoice($company, $invoice)) {
                $result[] = $error;
                continue;
            }

            $id = $invoice['id'] ?? null;
            $code = $invoice['cod'] ?? null;
            $shopId = $invoice['shopId'] ?? null;
            $shopCod = $invoice['shopCod'] ?? null;
            $buyerId = $invoice['buyerId'] ?? null;
            $buyerCod = $invoice['buyerCod'] ?? null;
            $date = $invoice['date'] ?? null;
            $number = $invoice['number'] ?? null;
            $products = $invoice['products'] ?? [];

            $shop = null;
            $buyer = null;

            if ($shopId) {
                $shop = $this->organizationService->getShopById($shopId);

            } else if ($shopCod) {
                $shop = $this->organizationService->getShopByCode($company, $shopCod);
            }

            if ($buyerId) {
                $buyer = $this->companyService->retrieveById($buyerId);

            } else if ($buyerCod) {
                $buyer = $this->companyService->retrieveByCode($buyerCod);
            }

            $data = new InvoiceData();
            $data
                ->setShop($shop)
                ->setBuyer($buyer ?? ($shop ? $shop->getCompany() : null))
                ->setNumber($number)
                ->setCode($code)
                ->setCreatedAt($date);

            foreach ($products as $product) {
                $productId = $product['productId'] ?? null;
                $productCode = $product['productCod'] ?? null;

                if ($productId) {
                    $productObject = $this->productService->getProductByCompany($company, $productId);

                } else {
                    $productObject = $this->productService->getProductByCode($company, $productCode);
                }

                $price = $product['price'] ?? 0;
                $vat = $product['vat'] ?? 0;

                if ($vat) {
                    $price = $price - ($price * $vat / ($vat + 100));
                }

                $productData = new InvoiceProductData();
                $productData
                    ->setProduct($productObject)
                    ->setPrice($price)
                    ->setVat($vat)
                    ->setQuantity($product['quantity'] ?? 0)
                    ->setTotalPriceVat($product['amountVat'] ?? 0)
                    ->setTotalPriceWithVat($product['amount'] ?? 0);

                $data->appendProduct($productData);
            }

            if ($code) {
                $invoice = $this->retrieveInvoiceByCode($company, $code);

            } else {
                $invoice = $this->retrieveInvoiceCompany($company, $id);
            }

            if ($invoice) {
                $invoiceObject = $this->updateInvoice($invoice, $data);

            } else {
                $invoiceObject = $this->create($company, $data);
            }

            $result[] = $invoiceObject;
        }

        return $result;
    }

    public function create(Company $company, InvoiceData $invoiceData): Invoice
    {
        $shop = $invoiceData->getShop();
        $buyer = $invoiceData->getBuyer();
        $storeHouseSetting = $buyer ? $buyer->getStoreHouseSetting() : null;

        $connection = Propel::getConnection();
        PropelHelper::startTransaction($connection);

        try {
            $invoice = new Invoice();
            $invoice
                ->setWarehouseId($storeHouseSetting ? $storeHouseSetting->getWarehouseId() : null)
                ->setNumber($invoiceData->getNumber())
                ->setCompanyRelatedBySupplierId($company)
                ->setCounterparty($invoiceData->getCounterparty())
                ->setExternalCode($invoiceData->getCode())
                ->setCompanyRelatedByBuyerId($buyer ?: ($shop ? $shop->getCompany() : null))
                ->setCompanyOrganizationShop($shop)
            ;

            if (strtotime($invoiceData->getCreatedAt())) {
                $invoice->setCreatedAt($invoiceData->getCreatedAt());
            }

            $invoice->save();

            $this->fillInvoiceProducts($invoice, $invoiceData);
            $this->sendNotification($invoice);

            PropelHelper::commitTransaction($connection);

        } catch (Exception $exception) {
            PropelHelper::rollBack($connection);

            throw $exception;
        }

        return $invoice;
    }

    public function createFromArray(array $data, $keyType = TableMap::TYPE_PHPNAME): Invoice
    {
        $invoice = new Invoice();
        $invoice->fromArray($data, $keyType);
        $invoice->save();

        return $invoice;
    }

    public function updateInvoice(Invoice $invoice, InvoiceData $invoiceData): Invoice
    {
        $shop = $invoiceData->getShop();
        $buyer = $invoiceData->getBuyer();

        $invoice
            ->setNumber($invoiceData->getNumber())
            ->setCounterparty($invoiceData->getCounterparty())
            ->setExternalCode($invoiceData->getCode())
            ->setCompanyRelatedByBuyerId($buyer ?: ($shop ? $shop->getCompany() : null))
            ->setCompanyOrganizationShop($shop)
        ;

        if (strtotime($invoiceData->getCreatedAt())) {
            $invoice->setCreatedAt($invoiceData->getCreatedAt());
        }

        $invoice->save();

        $this->fillInvoiceProducts($invoice, $invoiceData);

        return $invoice;
    }

    public function getComparisonProducts(Invoice $invoice): array
    {
        $products = InvoiceProductQuery::create()
            ->useInvoiceQuery(null, Criteria::INNER_JOIN)
                ->filterByCompanyRelatedByBuyerId($invoice->getCompanyRelatedByBuyerId())
                ->filterByCompanyRelatedBySupplierId($invoice->getCompanyRelatedBySupplierId())
            ->endUse()
            ->useInvoiceProductRelatedByIdQuery('ipc', Criteria::INNER_JOIN)
            ->endUse()
            ->distinct()
            ->find();

        $out = [];

        /** @var InvoiceProduct $product */
        foreach ($products as $product) {
            $out[$product->getProductId()] = $product->getInvoiceProductsRelatedById()->getFirst();
        }

        return $out;
    }

    public function fillInvoiceProducts(Invoice $invoice, InvoiceData $invoiceData): void
    {
        $comparisonProducts = $this->getComparisonProducts($invoice);
        $comparisonProductsData = [];

        $invoiceProductIds = [];

        /** @var InvoiceProductData $product */
        foreach ($invoiceData->getProducts() as $product) {
            $productObject = $product->getProduct();

            $productUnit = $productObject ? $productObject->getUnit() : null;
            $invoiceProduct = $product->getInvoiceProduct() ?: new InvoiceProduct();
            $invoiceProduct
                ->setInvoice($invoice)
                ->setProduct($productObject)
                ->setUnit($product->getUnit() ?: $productUnit)
                ->setQuantity($product->getQuantity())
                ->setVat($product->getVat())
                ->setTotalPrice($product->getTotalPrice())
                ->setTotalPriceWithVat($product->getTotalPriceWithVat())
                ->setTotalPriceVat($product->getTotalPriceVat())
                ->setPriceWithVat($product->getPriceWithVat())
                ->setPrice($product->getPrice() ?: $product->getQuantity() * ($productObject ? $productObject->getPrice() : 0))
                ->save();

            if ($productObject) {
                $comparisonProduct = $comparisonProducts[$productObject->getId()] ?? null;

                if ($comparisonProduct) {
                    $comparisonProductData = new InvoiceComparisonProductData();
                    $comparisonProductData
                        ->setUnit($comparisonProduct->getUnit())
                        ->setProduct($comparisonProduct->getProduct())
                        ->setComparisonRate($comparisonProduct->getComparisonRate())
                        ->setInvoiceProduct($invoiceProduct);

                    $comparisonProductsData[] = $comparisonProductData;
                }
            }

            $invoiceProductIds[] = $invoiceProduct->getId();
        }

        if ($comparisonProductsData) {
            $this->comparisonService->comparisonInvoiceProducts($comparisonProductsData);
        }

        if ($invoiceProductIds) {
            InvoiceProductQuery::create()
                ->filterByInvoice($invoice)
                ->filterById($invoiceProductIds, Criteria::NOT_IN)
                ->delete();
        }
    }

    public function createInvoiceProductFromArray(array $data, $keyType = TableMap::TYPE_PHPNAME): InvoiceProduct
    {
        $product = new InvoiceProduct();
        $product->fromArray($data, $keyType);
        $product->save();

        return $product;
    }

    protected function initInvoicesListQuery(InvoiceListContext $context): InvoiceQuery
    {
        $query = InvoiceQuery::create();
        $company = $context->getCompany();

        if ($company) {
            if ($company->isSupplierCompany()) {
                $query->filterByCompanyRelatedBySupplierId($company);

            } else {
                $query->filterByCompanyRelatedByBuyerId($company);
            }
        }

        return $query;
    }

    protected function filterInvoicesListQuery(InvoiceQuery $query, InvoiceListContext $context): void
    {
        if ($context->getNumber()) {
            $query->filterById($context->getNumber());
        }

        if ($context->getDateFrom()) {
            $query->filterByCreatedAt($context->getDateFrom(), Criteria::GREATER_EQUAL);
        }

        if ($context->getDateTo()) {
            $query->filterByCreatedAt($context->getDateTo(), Criteria::LESS_EQUAL);
        }

        if ($context->getDateChangeFrom()) {
            $query->filterByUpdatedAt($context->getDateChangeFrom(), Criteria::GREATER_EQUAL);
        }

        if ($context->getDateChangeTo()) {
            $query->filterByUpdatedAt($context->getDateChangeTo(), Criteria::LESS_EQUAL);
        }

        if ($relatedCompany = $context->getRelatedCompany()) {
            if ($relatedCompany->isSupplierCompany()) {
                $query->filterByCompanyRelatedBySupplierId($relatedCompany);

            } else {
                $query->filterByCompanyRelatedByBuyerId($relatedCompany);
            }
        }

        if ($context->getShop()) {
            $query->filterByCompanyOrganizationShop($context->getShop());
        }

        if ($context->getPriceFrom()) {
            $query->filterByPrice($context->getPriceFrom());
        }

        if ($context->getPriceTo()) {
            $query->filterByPrice($context->getPriceTo(), Criteria::LESS_EQUAL);
        }

        if ($context->getSuppliersId()) {
            $query->filterBySupplierId($context->getSuppliersId());
        }

        if ($context->getBuyersId()) {
            $query->filterByBuyerId($context->getBuyersId());
        }

        if ($context->getAcceptanceStatusId()) {
            $query->filterByAcceptanceStatusId($context->getAcceptanceStatusId());
        }

        if ($search = $context->getSearch()) {
            $query
                ->filterById("%{$search}%", Criteria::LIKE)->_or()
                ->filterByExternalCode("%{$search}%", Criteria::LIKE)->_or()
                ->filterByNumber("%{$search}%", Criteria::LIKE)
                ->distinct();
        }
    }

    public function getInvoicesList(InvoiceListContext $context, ListConfiguration $listConfiguration)
    {
        $query = $this->initInvoicesListQuery($context)->distinct();
        $this->filterInvoicesListQuery($query, $context);

        if ($sort = $context->getNormalizeSort()) {
            $tableMap = InvoiceTableMap::getTableMap();

            $sortField = $sort['field'];
            $sortDirection = $sort['direction'];

            if ($tableMap->hasColumnByPhpName(ucfirst($sortField))) {
                $query->orderBy($tableMap->getColumnByPhpName(ucfirst($sortField))->getName(), $sortDirection);

            } else if ($sortField === 'supplier') {
                $query
                    ->useCompanyRelatedBySupplierIdQuery(null, Criteria::LEFT_JOIN)
                        ->orderByTitle($sortDirection)
                    ->endUse();

            } else if ($sortField === 'organization') {
                $query
                    ->useCompanyOrganizationShopQuery(null, Criteria::LEFT_JOIN)
                        ->useCompanyOrganizationQuery(null, Criteria::LEFT_JOIN)
                            ->orderByTitle($sortDirection)
                        ->endUse()
                    ->endUse();

            } else if ($sortField === 'shop') {
                $query
                    ->useCompanyOrganizationShopQuery(null, Criteria::LEFT_JOIN)
                        ->orderByTitle($sortDirection)
                    ->endUse();

            } else if ($sortField === 'totalPrice') {
                $query
                    ->withColumn('(SELECT SUM(ip.price * ip.quantity) FROM invoice_product ip WHERE ip.invoice_id = invoice.id)', 'price')
                    ->orderBy('price', $sortDirection)
                ;

            } else if ($sortField === 'acceptedTotalPrice') {
                $query
                    ->withColumn('(
                        SELECT SUM(cp.price * cp.quantity)
                        FROM invoice_product cp
                        WHERE cp.comparison_id IN (SELECT CONCAT_WS(",", id) FROM invoice_product ip WHERE ip.invoice_id = invoice.id)
                    )', 'price')
                    ->orderBy('price', $sortDirection)
                ;

            } else if ($sortField === 'totalPriceWithVat') {
                $query
                    ->withColumn('(SELECT SUM(ip.price * ip.quantity * (IF(invoice.vat, invoice.vat, p.vat) / 100)) FROM invoice_product ip INNER JOIN product p ON ip.product_id = p.id WHERE ip.invoice_id = invoice.id)', 'price')
                    ->orderBy('price', $sortDirection)
                ;

            } else if ($sortField === 'acceptanceStatus') {
                $query
                    ->useInvoiceStatusRelatedByAcceptanceStatusIdQuery()
                        ->orderByTitle($sortDirection)
                    ->endUse()
                ;
            }
        }

        return $this->listConfigurationService->fetch($query, $listConfiguration);
    }

    public function getInvoiceStatuses($typeCode): ObjectCollection
    {
        return InvoiceStatusQuery::create()
            ->filterByType(InvoiceStatus::getTypeId($typeCode))
            ->filterByVisible(true)
            ->orderBySortableRank()
            ->find();
    }

    public function isFullComparison(Invoice $invoice): bool
    {
        $products = $invoice->getInvoiceProducts();

        foreach ($products as $product) {
            $comparisonProduct = $product->getInvoiceProductsRelatedById()->getFirst();

            if (!$comparisonProduct) {
                return false;
            }

            if (abs(PriceFormatHelper::format($product->getQuantity()) - PriceFormatHelper::format($comparisonProduct->getQuantityWithComparisonRate())) > PHP_FLOAT_EPSILON) {
                return false;
            }
        }

        return true;
    }

    public function saveColumns(User $user, array $columns): void
    {
        $user->setInvoiceColumns(array_filter($columns))->save();
    }

    public function sendNotification(Invoice $invoice): void
    {
        if (!$notification = $this->notificationService->retrieveByCode(Notification::CODE_INVOICE_NEW)) {
            return;
        }

        $buyer = $invoice->getCompanyRelatedByBuyerId();
        $users = $buyer->getCompanyUsersData();

        $text = strtr($notification->getText(), [
            '#supplierTitle#' => $buyer->getTitle(),
            '#buyerInn#' => $buyer->getInn(),
            '#number#' => $invoice->getId(),
        ]);

        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $data = $this->dataObjectBuilder->build(NotificationUserData::class, [
                'user' => $user,
                'notification' => $notification,
                'link' => $this->urlGenerator->generate('invoice-id', ['id' => $invoice->getId()], Router::ABSOLUTE_URL),
                'invoice' => $invoice,
                'text' => $text,
            ]);

            $userNotification = $this->notificationService->createUserNotification($data);

            $this->eventPublisher->publish(
                $user,
                $userNotification,
                [
                    EventPublisher::MESSAGE_TYPE => NotificationService::EVENT_MESSAGE_TYPE_NEW_NOTIFICATION
                ]
            );
        }

        if (isset($userNotification)) {
            $this->notificationService->doDuplicateByEmail($buyer, $userNotification);
        }
    }

    public function getInvoicesFromImport(Company $company): array
    {
        return InvoiceQuery::create()
            ->filterByCompanyRelatedByBuyerId($company)
            ->filterByExternalCode('', Criteria::NOT_EQUAL)
            ->find()
            ->toKeyIndex('ExternalCode');
    }

    public function setInvoiceDischargeStatus(Invoice $invoice, string $statusCode): Invoice
    {
        if (!$status = InvoiceStatus::retrieveByCode($statusCode)) {
            return $invoice;
        }

        $invoice
            ->setInvoiceStatusRelatedByDischargeStatusId($status)
            ->save();

        return $invoice;
    }

    public function exchangeInvoice(Invoice $invoice): void
    {
        $company = $invoice->getCompanyRelatedByBuyerId();

        $iikoSetting = $company->getIikoSetting();
        $storeHouseSetting = $company->getStoreHouseSetting();

        $errorScopes = [];
        $availableSystems = 0;

        if ($iikoSetting->getLogin()) {
            $availableSystems++;
            try {
                $this->iikoInvoiceService->add($iikoSetting, $invoice);
                $this->setInvoiceDischargeStatus($invoice, InvoiceStatus::CODE_DISCHARGE);
            } catch (Exception $exception) {
                $errorScopes[] = 'Iiko';
            }
        }

        if ($storeHouseSetting->getLogin()) {
            $availableSystems++;
            try {
                $this->storeHouseInvoiceService->add($storeHouseSetting, $invoice);
                $this->setInvoiceDischargeStatus($invoice, InvoiceStatus::CODE_DISCHARGE);
            } catch (Exception $exception) {
                $errorScopes[] = 'StoreHouse';
            }
        }

        if (count($errorScopes) === $availableSystems) {
            $this->setInvoiceDischargeStatus($invoice, InvoiceStatus::CODE_NOT_DISCHARGE);

            throw new InvoiceExchangeException(
                'Не удаётся выгрузить накладную , напишите в техническую поддержку'
            );
        }
    }

    public function deleteInvoices(Company $company, array $filters)
    {
        $ids = $filters['id'] ?? null;
        $codes = $filters['cod'] ?? null;

        $invoiceQuery = InvoiceQuery::create()->filterByCompanyRelatedBySupplierId($company);

        if ($ids) {
            $invoiceQuery->filterById($ids)->delete();
        }

        if ($codes) {
            $invoiceQuery->filterByExternalCode($codes)->delete();
        }
    }

    public function getFilterInvoices(array $filters)
    {
        $query = InvoiceQuery::create();

        $ids = $filters['id'] ?? null;
        $codes = $filters['cod'] ?? null;
        $dateFrom = $filters['dateFrom'] ?? null;

        if ($ids) {
            $query->filterById($ids);
        }

        if ($codes) {
            $query->filterByExternalCode($codes);
        }

        if ($dateFrom && strtotime($dateFrom)) {
            $query->filterByCreatedAt($dateFrom, Criteria::GREATER_EQUAL);
        }

        return $query->find();
    }

    private function validateInvoice(Company $company, array $invoice): ?array
    {
        $id = $invoice['id'] ?? null;
        $code = $invoice['cod'] ?? null;
        $shopId = $invoice['shopId'] ?? null;
        $shopCod = $invoice['shopCod'] ?? null;
        $buyerId = $invoice['buyerId'] ?? null;
        $buyerCod = $invoice['buyerCod'] ?? null;
        $date = $invoice['date'] ?? null;
        $number = $invoice['number'] ?? null;

        $error = [
            'id' => $id,
            'cod' => $code,
        ];

        if (!$code && $id && !$this->retrieveInvoiceCompany($company, $id)) {
            $error['message'] = 'Накладная не найдена';
            return $error;
        }

        $shop = null;

        if ($shopId && !$shop = $this->organizationService->getShopById($shopId)) {
            $error['message'] = sprintf('Торговая точка с ID %d не найдена', $shopId);
            return $error;

        } else if ($shopCod && !$shop = $this->organizationService->getShopByCode($company, $shopCod)) {
            $error['message'] = sprintf('Торговая точка с кодом %s не найдена', $shopCod);
            return $error;
        }

        $buyer = null;

        if ($buyerId && !$buyer = $this->companyService->retrieveById($buyerId)) {
            $error['message'] = sprintf('Покупатель с ID %d не найден', $buyerId);
            return $error;

        } else if ($buyerCod && !$shop = $this->companyService->retrieveByCode($buyerCod)) {
            $error['message'] = sprintf('Покупатель с кодом %s не найден', $buyerCod);
            return $error;
        }

        if (!$buyerId && !$buyerCod && !$shopId && !$shopCod) {
            $error['message'] = 'Укажите buyerId || buyerCod || shopId || shopCod';
            return $error;
        }

        if (!$buyer && !$shop) {
            $error['message'] = 'Не найден покупатель либо торговая точка';
            return $error;
        }

        if (!$date || !strtotime($date)) {
            $error['message'] = 'Введите корректную дату';
            return $error;
        }

        if (!$number) {
            $error['message'] = 'Укажите номер';
            return $error;
        }

        return null;
    }
}
