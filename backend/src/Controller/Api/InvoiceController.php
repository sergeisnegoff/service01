<?php


namespace App\Controller\Api;


use App\Model\CompanyUserRule;
use App\Model\InvoiceStatus;
use App\Normalizer\InvoiceNormalizer;
use App\Normalizer\InvoiceProductNormalizer;
use App\Service\Buyer\BuyerOrganizationService;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\Company\CompanyRepository;
use App\Service\Company\CompanyUserRuleService;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\Invoice\Exception\InvoiceExchangeException;
use App\Service\Invoice\InvoiceAcceptData\InvoiceAcceptData;
use App\Service\Invoice\InvoiceAcceptService;
use App\Service\Invoice\InvoiceComparisonData\InvoiceComparisonData;
use App\Service\Invoice\InvoiceComparisonService;
use App\Service\Invoice\InvoiceData\InvoiceData;
use App\Service\Invoice\InvoiceList\InvoiceListContext;
use App\Service\Invoice\InvoicePackService;
use App\Service\Invoice\InvoiceService;
use App\Service\ListConfiguration\ListConfiguration;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @Route("/invoices")
 */
class InvoiceController extends AbstractController
{
    /**
     * Список накладных
     *
     * @QueryParameter("search", type="string", description="Поиск")
     * @QueryParameter("number", type="string", description="Номер накладной")
     * @QueryParameter("dateFrom", type="string", description="Дата (от)")
     * @QueryParameter("dateTo", type="string", description="Дата (до)")
     *
     * @QueryParameter("dateChangeFrom", type="string", description="Дата изменения (от)")
     * @QueryParameter("dateChangeTo", type="string", description="Дата изменения (до)")
     *
     * @QueryParameter("companyId", type="string", description="ID покупателя/поставщика")
     * @QueryParameter("organizationId", type="string", description="ID организации")
     * @QueryParameter("shopId", type="string", description="ID торговой точки")
     * @QueryParameter("priceFrom", type="string", description="Цена (от)")
     * @QueryParameter("priceTo", type="string", description="Цена (до)")
     * @QueryParameter("acceptanceStatusId", type="string", description="Статус принятия")
     *
     * @QueryParameter("sortField", type="string", description="Поле сортировки")
     * @QueryParameter("sortDirection", type="string", description="Направление сортировки")
     *
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("", methods={"GET"})
     */
    public function getInvoices(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        InvoiceService $invoiceService,
        ListConfiguration $listConfiguration,
        BuyerOrganizationService $buyerOrganizationService,
        CompanyRepository $companyRepository
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();
        $request = $handler->getRequest();

        $context = new InvoiceListContext();
        $context
            ->setCompany($company)
            ->setPriceFrom((float)$request->query->get('priceFrom'))
            ->setPriceTo((float)$request->query->get('priceTo'))
            ->setDateFrom(strtotime($request->query->get('dateFrom')) ? new \DateTime($request->query->get('dateFrom')) : null)
            ->setDateTo(strtotime($request->query->get('dateTo')) ? new \DateTime($request->query->get('dateTo')) : null)
            ->setDateChangeFrom(strtotime($request->query->get('dateChangeFrom')) ? new \DateTime($request->query->get('dateChangeFrom')) : null)
            ->setDateChangeTo(strtotime($request->query->get('dateChangeTo')) ? new \DateTime($request->query->get('dateChangeTo')) : null)
            ->setAcceptanceStatusId($request->query->getInt('acceptanceStatusId'))
            ->setNumber((string)$request->query->get('number'))
            ->setShop($buyerOrganizationService->getShopById($request->query->get('shopId')))
            ->setRelatedCompany($companyRepository->findPk($request->query->get('companyId')))
            ->setSortField($request->query->get('sortField', ''))
            ->setSortDirection($request->query->get('sortDirection', ''))
            ->setSearch($request->query->get('search', ''))
        ;

        return $handler->response($invoiceService->getInvoicesList($context, $listConfiguration));
    }

    /**
     * @QueryParameter("id", type="integer", description="ID накладной")
     * @QueryParameter("cod", type="string", description="Cod накладной")
     *
     * @Route("/info", methods={"GET"})
     */
    public function getInvoiceInfo(RestHandler $handler, InvoiceService $invoiceService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();
        $request = $handler->getRequest();
        $invoice = null;

        if ($id = $request->query->get('id')) {
            $invoice = $invoiceService->retrieveInvoiceCompanySupplierOrBuyerById($company, $id);

        } else if ($code = $request->query->get('cod')) {
            $invoice = $invoiceService->retrieveInvoiceCompanySupplierOrBuyerByCode($company, $code);
        }

        $handler->checkFound($invoice);
        $handler->data->addGroup(InvoiceNormalizer::GROUP_EXTERNAL);

        return $handler->response($invoice);
    }

    /**
     * Список всех накладных
     *
     * @Route("/all", methods={"POST"})
     */
    public function getAllInvoices(
        RestHandler $handler,
        InvoiceService $invoiceService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $filter = $request->request->get('filters', []);

        $handler->data->addGroup(InvoiceNormalizer::GROUP_EXTERNAL);

        return $handler->response($invoiceService->getFilterInvoices($filter));
    }

    /**
     * Список накладных зарегистрированных к выгрузке
     *
     * @Route("/new", methods={"GET"})
     */
    public function getNewInvoices(RestHandler $handler, InvoicePackService $packService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $handler->data->addGroup(InvoiceNormalizer::GROUP_EXTERNAL);
        $handler->data->addGroup(InvoiceProductNormalizer::GROUP_EXTERNAL);

        return $handler->response($packService->getInvoices($companyStorage->getCompany()));
    }

    /**
     * @Route("/change", methods={"POST"})
     */
    public function changeInvoiceCode(RestHandler $handler, InvoiceService $invoiceService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $invoices = $request->request->get('invoices', []);
        $handler->data->addGroup(InvoiceNormalizer::GROUP_MASS_ADDITION);

        return $handler->response($invoiceService->changeInvoices($companyStorage->getCompany(), $invoices));
    }

    /**
     * @Route("/delete", methods={"POST"})
     */
    public function delete(RestHandler $handler, InvoiceService $invoiceService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $filter = $request->request->get('filters', []);
        $invoiceService->deleteInvoices($companyStorage->getCompany(), $filter);

        return $handler->response();
    }

    /**
     * Очистить список накладных зарегистрированных к выгрузке
     *
     * @PathParameter("numberMessage", type="integer", description="Номер сообщения")
     * @Route("/new/clear/{numberMessage}", methods={"GET"})
     */
    public function clearNewInvoices(RestHandler $handler, InvoicePackService $packService, ActiveCompanyStorage $companyStorage, $numberMessage)
    {
        $handler->checkAuthorization();
        $packService->clearPacks($companyStorage->getCompany(), (int) $numberMessage);

        return $handler->response();
    }

    /**
     * Создание / редактирование накладной
     *
     * @RequestParameter("invoiceId", type="string", description="ID накладной")
     *
     * @RequestParameter("buyerId", type="string", description="ID покупателя")
     * @RequestParameter("code", type="string", description="Внешний код")
     * @RequestParameter("shopId", type="string", description="ID торговой точки")
     * @RequestParameter("products", type="string", description="Массив данных о товарах")
     * @RequestParameter("createdAt", type="string", description="Дата создания")
     * @RequestParameter("unitId", type="string", description="ID ед. измерения")
     *
     * @RequestParameter("findByCode", type="boolean", description="Поиск по внешнему коду")
     *
     * @Route("", methods={"POST"})
     */
    public function createInvoices(
        RestHandler $handler,
        DataObjectBuilder $dataObjectBuilder,
        ActiveCompanyStorage $companyStorage,
        InvoiceService $invoiceService
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();

        $handler->checkPermission('supplier', $user);
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        $invoices = $request->request->get('invoices', []);

        if (!$invoices) {
            $data = $dataObjectBuilder->build(InvoiceData::class, $request->request->all());
            $data->initProducts();
            $data->initEntities($request->request);

            foreach ($data->validate($company) as $key => $error) {
                $handler->error->set(sprintf('request/%s', $key), $error);
            }

            $handler->error->send();

            $invoice = $request->request->getBoolean('findByCode') ?
                $invoiceService->retrieveInvoiceByCode($company, $request->request->get('code')) :
                $invoiceService->retrieveInvoiceCompany($company, $request->request->get('invoiceId'));

            if ($invoice) {
                $invoiceService->updateInvoice($invoice, $data);

            } else {
                $invoice = $invoiceService->create($company, $data);
            }

        } else {
            $handler->data->addGroup(InvoiceNormalizer::GROUP_MASS_ADDITION);
            $invoice = $invoiceService->createInvoices($company, $invoices);
        }

        $handler->data->addGroup(InvoiceNormalizer::GROUP_DETAIL);

        return $handler->response($invoice);
    }

    /**
     * Статусы накладных
     *
     * @QueryParameter("code", type="string", description="Код для получения нужного списка")
     *
     * @Route("/statuses", methods={"GET"})
     */
    public function getInvoiceStatuses(RestHandler $handler, InvoiceService $invoiceService)
    {
        $handler->checkAuthorization();
        $handler->validate([
            'query' => [
                'code' => [
                    new NotBlank(),
                    new Choice([
                        'choices' => InvoiceStatus::$typeCodes,
                        'message' => sprintf('Допустимые значения %s', implode(', ', InvoiceStatus::$typeCodes))
                    ])
                ],
            ]
        ]);

        $request = $handler->getRequest();

        return $handler->response($invoiceService->getInvoiceStatuses($request->query->get('code')));
    }

    /**
     * Записать колонки
     *
     * @RequestParameter("columns", type="string", description="Массив кодов колонок")
     *
     * @Route("/columns", methods={"POST"})
     */
    public function saveColumns(RestHandler $handler, InvoiceService $invoiceService)
    {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'columns' => [new NotBlank(), new Type(['type' => 'array'])],
            ]
        ]);

        $request = $handler->getRequest();
        $user = $this->getUser();

        $invoiceService->saveColumns($user, $request->request->get('columns', []));

        return $handler->response($user);
    }

    /**
     * Получить колонки
     *
     * @Route("/columns", methods={"GET"})
     */
    public function getColumns(RestHandler $handler)
    {
        $handler->checkAuthorization();
        $user = $this->getUser();

        return $handler->response([
            'columns' => array_filter($user->getInvoiceColumns())
        ]);
    }

    /**
     * Сопоставление накладной
     *
     * @PathParameter("id", type="integer", description="ID накладной")
     *
     * @RequestParameter("products", type="string", description="Массив данных о сопоставлении")
     *
     * @RequestParameter("warehouseId", type="string", description="ID склада")
     * @RequestParameter("counterpartyId", type="string", description="ID контрагента")
     * @RequestParameter("acceptanceStatusId", type="integer", description="Статус приемки")
     * @RequestParameter("dischargeStatusId", type="integer", description="Статус выгрузки")
     *
     * @RequestParameter("payUpTo", type="string", description="Оплатить до")
     * @RequestParameter("comment", type="string", description="Комментарий")
     *
     * @RequestParameter("discharge", type="boolean", description="Сохранить и отправить")
     * @RequestParameter("cancel", type="boolean", description="Аннулировать и закрыть")
     *
     * @Route("/{id}/comparison", methods={"POST"})
     */
    public function mappingInvoices(
        RestHandler $handler,
        InvoiceService $invoiceService,
        InvoiceComparisonService $comparisonService,
        ActiveCompanyStorage $companyStorage,
        DataObjectBuilder $dataObjectBuilder,
        CompanyUserRuleService $ruleService,
        $id
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();
        $handler->checkPermission('buyer', $user);
        $ruleService->isGranted($handler, CompanyUserRule::RULE_INVOICE);

        $request = $handler->getRequest();

        $invoice = $invoiceService->retrieveInvoice($id);

        if (!$invoice || !$invoice->isOwner($companyStorage->getCompany())) {
            $handler->error->notFound();
        }

        /** @var InvoiceComparisonData $data */
        $data = $dataObjectBuilder->build(InvoiceComparisonData::class, $request->request->all());
        $data
            ->setDischarge($request->request->getBoolean('discharge'))
            ->setCancel($request->request->getBoolean('cancel'))
            ->setCounterparty()
            ->setWarehouse()
        ;

        $handler->data->addGroup(InvoiceNormalizer::GROUP_DETAIL);
        $handler->data->addGroup(InvoiceNormalizer::GROUP_COMPARISON);

        return $handler->response($comparisonService->processComparisonInvoice($invoice, $data));
    }

    /**
     * Приемка накладной
     *
     * @PathParameter("id", type="integer", description="ID накладной")
     *
     * @RequestParameter("products", type="string", description="Массив данных о сопоставлении")
     *
     * @RequestParameter("acceptanceStatusId", type="integer", description="Статус приемки")
     * @RequestParameter("dischargeStatusId", type="integer", description="Статус выгрузки")
     * @RequestParameter("egaisStatus", type="integer", description="Статус EGAIS")
     *
     * @RequestParameter("acceptanceAt", type="string", description="Дата приемки")
     * @RequestParameter("hasAccepted", type="string", description="Принял")
     * @RequestParameter("linkOrder", type="string", description="Связан с заказом")
     *
     * @RequestParameter("save", type="boolean", description="Сохранить и закрыть")
     * @RequestParameter("cancel", type="boolean", description="Аннулировать и закрыть")
     *
     * @Route("/{id}/accept", methods={"POST"})
     */
    public function acceptInvoices(
        RestHandler $handler,
        InvoiceService $invoiceService,
        InvoiceAcceptService $invoiceAcceptService,
        ActiveCompanyStorage $companyStorage,
        DataObjectBuilder $dataObjectBuilder,
        CompanyUserRuleService $ruleService,
        $id
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();
        $handler->checkPermission('buyer', $user);
        $ruleService->isGranted($handler, CompanyUserRule::RULE_INVOICE);

        $request = $handler->getRequest();

        $invoice = $invoiceService->retrieveInvoice($id);

        if (!$invoice || !$invoice->isOwner($companyStorage->getCompany())) {
            $handler->error->notFound();
        }

        /** @var InvoiceAcceptData $data */
        $data = $dataObjectBuilder->build(InvoiceAcceptData::class, $request->request->all());
        $data->setCancel($request->request->getBoolean('cancel'));
        $data->setSave($request->request->getBoolean('save'));

        $handler->data->addGroup(InvoiceNormalizer::GROUP_DETAIL);
        $handler->data->addGroup(InvoiceNormalizer::GROUP_COMPARISON);

        return $handler->response($invoiceAcceptService->processAcceptInvoice($invoice, $data));
    }

    /**
     * Отправить накладную в сервисы
     *
     * @PathParameter("id", type="integer", description="ID накладной")
     *
     * @Route("/{id}/exchange", methods={"POST"})
     */
    public function exchangeInvoice(
        RestHandler $handler,
        InvoiceService $invoiceService,
        InvoicePackService $packService,
        $id
    ) {
        $invoice = $invoiceService->retrieveInvoice($id);
        $handler->checkFound($invoice);

        try {
            if (!$pack = $packService->getInvoicePack($invoice)) {
                $packService->createPack($invoice);

            } else {
                $packService->resetPack($pack);
            }

            $invoiceService->exchangeInvoice($invoice);

        } catch (InvoiceExchangeException $exception) {
            $handler->error->send($exception->getMessage());
        }

        return $handler->response();
    }

    /**
     * Информация о накладной
     *
     * @PathParameter("id", type="integer", description="ID накладной")
     * @QueryParameter("withComparison", type="bool", description="Получить информацию для сопоставления")
     *
     * @Route("/{id}", methods={"GET"})
     */
    public function getInvoice(RestHandler $handler, InvoiceService $invoiceService, $id)
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $handler->data->addGroup(InvoiceNormalizer::GROUP_DETAIL);

        if ($request->query->getBoolean('withComparison')) {
            $handler->data->addGroup(InvoiceNormalizer::GROUP_COMPARISON);
        }

        return $handler->response($invoiceService->retrieveInvoice($id));
    }
}
