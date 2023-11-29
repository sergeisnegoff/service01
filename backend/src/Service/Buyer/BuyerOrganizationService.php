<?php


namespace App\Service\Buyer;


use App\EventPublisher\EventPublisher;
use App\Model\Company;
use App\Model\CompanyOrganizationShop;
use App\Model\CompanyOrganizationShopQuery;
use App\Model\CompanyShopCode;
use App\Model\CompanyShopCodeQuery;
use App\Model\Notification;
use App\Service\Buyer\BuyerOrganizationData\BuyerOrganizationShopData;
use App\Service\Buyer\Event\AfterCreateOrganizationShopEvent;
use App\Service\Company\CompanyService;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Notification\NotificationService;
use App\Service\Notification\NotificationUserData;
use App\Service\Supplier\SupplierList\SupplierListContext;
use App\Service\Supplier\SupplierService;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class BuyerOrganizationService
{
    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;
    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;
    /**
     * @var SupplierService
     */
    private SupplierService $supplierService;
    private DataObjectBuilder $dataObjectBuilder;
    private CompanyService $companyService;

    public function __construct(
        ListConfigurationService $listConfigurationService,
        EventDispatcherInterface $dispatcher,
        EventPublisher $eventPublisher,
        NotificationService $notificationService,
        UrlGeneratorInterface $urlGenerator,
        SupplierService $supplierService,
        DataObjectBuilder $dataObjectBuilder,
        CompanyService $companyService
    ) {
        $this->listConfigurationService = $listConfigurationService;
        $this->dispatcher = $dispatcher;
        $this->eventPublisher = $eventPublisher;
        $this->notificationService = $notificationService;
        $this->urlGenerator = $urlGenerator;
        $this->supplierService = $supplierService;
        $this->dataObjectBuilder = $dataObjectBuilder;
        $this->companyService = $companyService;
    }

    public function getShopById($id): ?CompanyOrganizationShop
    {
        $query = CompanyOrganizationShopQuery::create();

        if (is_numeric($id)) {
            return $query->findPk($id) ?? $query->findOneByExternalCode($id);
        }

        return $query->findOneByExternalCode($id);
    }

    public function getShopByCode(Company $company, $code): ?CompanyOrganizationShop
    {
        return CompanyOrganizationShopQuery::create()
            ->useCompanyShopCodeQuery()
                ->filterByCompany($company)
                ->filterByExternalCode($code)
            ->endUse()
            ->findOne();
    }

    public function getShopsByPks(array $pks): ObjectCollection
    {
        return CompanyOrganizationShopQuery::create()->findPks($pks);
    }

    public function getShopsForSmartImport(Company $company): array
    {
        return CompanyOrganizationShopQuery::create()
            ->filterByExternalCode('', Criteria::NOT_EQUAL)
            ->filterByCompany($company)
            ->distinct()
            ->find()
            ->toKeyIndex('ExternalCode');
    }

    public function createOrganizations(Company $company, array $organizations): array
    {
        $result = [];

        foreach ($organizations as $organization) {
            if ($error = $this->validateOrganization($organization)) {
                $result[] = $error;
                continue;
            }

            $id = $organization['id'] ?? null;
            $code = $organization['cod'] ?? '';
            $newCode = $organization['newCod'] ?? '';
            $alternativeTitle = $organization['alternativeTitle'] ?? '';

            if ($id) {
                $existOrganization = $this->companyService->retrieveById($id);

            } else if ($code) {
                $existOrganization = $this->companyService->retrieveByCommentExternalCode($code);
            }

            if ($existOrganization) {
                $this->companyService->commentCompany(
                    $company,
                    $existOrganization,
                    '',
                    $newCode ?: $code,
                    $alternativeTitle
                );

                $result[] = [
                    'id' => $existOrganization->getId(),
                    'cod' => $newCode ?: $code,
                ];
            }
        }

        return $result;
    }

    public function createShops(Company $company, array $shops): array
    {
        $result = [];

        foreach ($shops as $shop) {
            if ($error = $this->validateShop($company, $shop)) {
                $result[] = $error;
                continue;
            }

            $id = $shop['id'] ?? null;
            $code = $shop['cod'] ?? null;
            $newCode = $shop['newCod'] ?? null;

            $data = new BuyerOrganizationShopData();
            $data
                ->setAlternativeTitle($shop['alternativeTitle'] ?? '')
                ->setCode((string) ($newCode ?: $code));

            if ($id) {
                $existShop = $this->getShopById($id);

            } else if ($code) {
                $existShop = $this->getShopByCode($company, $code);
            }

            if (isset($existShop) && $existShop) {
                $this->fillShopCode($company, $existShop, $data);
                $result[] = $existShop;
            }
        }

        return $result;
    }

    public function createOrganizationShop(Company $company, BuyerOrganizationShopData $shopData): CompanyOrganizationShop
    {
        $shop = new CompanyOrganizationShop();
        $shop
            ->setAlternativeTitle($shopData->getAlternativeTitle())
            ->setDiadocExternalCode($shopData->getDiadocExternalCode())
            ->setDocrobotExternalCode($shopData->getDocrobotExternalCode())
            ->setCompany($company)
            ->setTitle($shopData->getTitle())
            ->setAddress($shopData->getAddress())
            ->setLatitude($shopData->getLatitude())
            ->setLongitude($shopData->getLongitude())
            ->setExternalCode($shopData->getCode())
            ->setPartnerTitle($shopData->getPartnerTitle())
            ->setFromSmart($shopData->isFromSmart())
            ->save();

        $this->dispatcher->dispatch((new AfterCreateOrganizationShopEvent())->setShop($shop));

        return $shop;
    }

    public function editOrganizationShop(CompanyOrganizationShop $shop, BuyerOrganizationShopData $shopData): CompanyOrganizationShop
    {
        if ($shopData->getTitle()) {
            $shop->setTitle($shopData->getTitle());
        }

        if ($shopData->getAlternativeTitle()) {
            $shop->setAlternativeTitle($shopData->getAlternativeTitle());
        }

        if ($shopData->getDiadocExternalCode()) {
            $shop->setDiadocExternalCode($shopData->getDiadocExternalCode());
        }

        if ($shopData->getDocrobotExternalCode()) {
            $shop->setDocrobotExternalCode($shopData->getDocrobotExternalCode());
        }

        if ($shopData->getAddress()) {
            $shop->setAddress($shopData->getAddress());
        }

        if ($shopData->getLatitude()) {
            $shop->setLatitude($shopData->getLatitude());
        }

        if ($shopData->getLongitude()) {
            $shop->setLongitude($shopData->getLongitude());
        }

        $shop
            ->setDiadocExternalCode($shopData->getDiadocExternalCode())
            ->setDocrobotExternalCode($shopData->getDocrobotExternalCode())
            ->setExternalCode($shopData->getCode())
            ->save();

        return $shop;
    }

    public function getAlternativeTitleShopText(Company $company, CompanyOrganizationShop $shop): string
    {
        $title = $this->getAlternativeTitleShop($company, $shop);
        return $title ? $title->getAlternativeTitle() : '';
    }

    // TODO: 2 таблицы company_shop_code и company_shop_title. Не понял зачем нужна вторая, если есть первая. Пофиксить если есть какой-то смысл
    public function getAlternativeTitleShop(Company $company, CompanyOrganizationShop $shop): ?CompanyShopCode
    {
        return CompanyShopCodeQuery::create()
            ->filterByCompany($company)
            ->filterByCompanyOrganizationShop($shop)
            ->findOne();
    }

    public function setAlternativeTitleShop(Company $company, CompanyOrganizationShop $shop, $title = ''): CompanyOrganizationShop
    {
        $alternativeTitle = $this->getAlternativeTitleShop($company, $shop) ?: new CompanyShopCode();

        if ($alternativeTitle->isNew()) {
            $alternativeTitle
                ->setCompany($company)
                ->setCompanyOrganizationShop($shop);
        }

        $alternativeTitle->setAlternativeTitle($title)->save();

        return $shop;
    }

    public function deleteShop(CompanyOrganizationShop $shop)
    {
        $shop->delete();
    }

    public function getCompanyOrganizationShops(Company $company, ListConfiguration $configuration)
    {
        return $this->listConfigurationService->fetch(CompanyOrganizationShopQuery::create()->filterByCompany($company), $configuration);
    }

    public function sendNewShopNotification(CompanyOrganizationShop $shop)
    {
        if (!$notification = $this->notificationService->retrieveByCode(Notification::CODE_NEW_ORGANIZATION_SHOP)) {
            return;
        }

        $company = $shop->getCompany();
        $users = $this->getSupplierUsers($company);
        $link = $this->urlGenerator->generate('buyer-id-organizations', ['id' => $company->getId()], Router::ABSOLUTE_URL);

        foreach ($users as $user) {
            $data = $this->dataObjectBuilder->build(NotificationUserData::class, [
                'user' => $user,
                'notification' => $notification,
                'link' => $link,
                'shop' => $shop,
                'buyer' => $company
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
            $this->notificationService->doDuplicateByEmail($company, $userNotification);
        }
    }

    public function getCompanyShopCode(Company $company, CompanyOrganizationShop $shop)
    {
        return CompanyShopCodeQuery::create()
            ->filterByCompany($company)
            ->filterByCompanyOrganizationShop($shop)
            ->findOne();
    }

    protected function getSupplierUsers(Company $company)
    {
        $context = new SupplierListContext();
        $context
            ->setCompany($company)
            ->setMySuppliers(true);

        $suppliers = $this->supplierService->getSupplierList($context, new ListConfiguration())->getData();

        $data = [];

        foreach ($suppliers as $supplier) {
            $data = array_merge($data, $supplier->getCompanyUsersData());
        }

        return $data;
    }

    private function validateOrganization(array $organization): ?array
    {
        $id = $organization['id'] ?? null;
        $code = $organization['cod'] ?? null;

        $error = [
            'id' => $id,
            'cod' => $code,
        ];

        if ($id && !$this->companyService->retrieveById($id)) {
            $error['message'] = sprintf('Организация с ID %d не найдена', $id);
            return $error;

        } else if ($code && !$this->companyService->retrieveByCommentExternalCode($code)) {
            $error['message'] = sprintf('Организация с кодом %s не найдена', $code);
            return $error;
        }

        return null;
    }

    private function validateShop(Company $company, array $shop): ?array
    {
        $id = $shop['id'] ?? null;
        $code = $shop['cod'] ?? null;

        $error = [
            'id' => $id,
            'cod' => $code,
        ];

        if ($id && !$this->getShopById($id)) {
            $error['message'] = sprintf('Торговая точка с ID %d не найдена', $id);
            return $error;

        } else if ($code && !$this->getShopByCode($company, $code)) {
            $error['message'] = sprintf('Торговая точка с кодом %s не найдена', $code);
            return $error;
        }

        return null;
    }

    private function fillShopCode(Company $company, CompanyOrganizationShop $shop, BuyerOrganizationShopData $data)
    {
        $companyCode = $this->getCompanyShopCode($company, $shop);

        if (!$companyCode) {
            $companyCode = new CompanyShopCode();
            $companyCode
                ->setCompany($company)
                ->setCompanyOrganizationShop($shop);
        }

        $companyCode
            ->setAlternativeTitle($data->getAlternativeTitle())
            ->setExternalCode($data->getCode())
            ->save();
    }
}
