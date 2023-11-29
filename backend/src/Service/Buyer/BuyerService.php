<?php


namespace App\Service\Buyer;


use App\EventPublisher\EventPublisher;
use App\Model\BuyerJobRequest;
use App\Model\BuyerJobRequestQuery;
use App\Model\Company;
use App\Model\CompanyQuery;
use App\Model\Notification;
use App\Model\UserGroup;
use App\Service\Buyer\BuyerList\BuyerListContext;
use App\Service\Buyer\Event\AfterCreateJobRequestEvent;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Notification\NotificationService;
use App\Service\Notification\NotificationUserData;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class BuyerService
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
     * @var NotificationService
     */
    private NotificationService $notificationService;
    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;
    private DataObjectBuilder $dataObjectBuilder;

    public function __construct(
        ListConfigurationService $listConfigurationService,
        EventDispatcherInterface $dispatcher,
        NotificationService $notificationService,
        EventPublisher $eventPublisher,
        UrlGeneratorInterface $urlGenerator,
        DataObjectBuilder $dataObjectBuilder
    ) {
        $this->listConfigurationService = $listConfigurationService;
        $this->dispatcher = $dispatcher;
        $this->notificationService = $notificationService;
        $this->eventPublisher = $eventPublisher;
        $this->urlGenerator = $urlGenerator;
        $this->dataObjectBuilder = $dataObjectBuilder;
    }

    public function getBuyersList(BuyerListContext $context, ListConfiguration $configuration)
    {
        $query = CompanyQuery::create()
            ->filterByType(Company::TYPE_BUYER)
            ->filterByVisible(true)
            ->distinct();

        if ($search = $context->getQuery()) {
            $query->filterByTitle('%' . $search . '%', Criteria::LIKE);
        }

        if ($context->getCompany() && $context->isFavorite()) {
            $query
                ->useCompanyFavoriteRelatedByFavoriteIdQuery()
                    ->filterByCompanyRelatedByCompanyId($context->getCompany())
                ->endUse();
        }

        if ($context->getCompany() && $context->isMyBuyers()) {
            $query
                ->useBuyerJobRequestRelatedByBuyerIdQuery()
                    ->filterByCompanyRelatedBySupplierId($context->getCompany())
                ->endUse()
                ->distinct();
        }

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function existBuyerJobRequest(Company $buyer, Company $supplier): bool
    {
        return BuyerJobRequestQuery::create()
            ->filterByCompanyRelatedByBuyerId($buyer)
            ->filterByCompanyRelatedBySupplierId($supplier)
            ->exists();
    }

    public function getBuyerJobRequest(Company $buyer, Company $supplier): ?BuyerJobRequest
    {
        return BuyerJobRequestQuery::create()
            ->filterByCompanyRelatedByBuyerId($buyer)
            ->filterByCompanyRelatedBySupplierId($supplier)
            ->findOne();
    }

    public function createBuyerJobRequest(Company $buyer, Company $supplier, string $text): BuyerJobRequest
    {
        $jobRequest = new BuyerJobRequest();
        $jobRequest
            ->setCompanyRelatedByBuyerId($buyer)
            ->setCompanyRelatedBySupplierId($supplier)
            ->setText($text)
            ->save();

        $this->dispatcher->dispatch((new AfterCreateJobRequestEvent())->setJobRequest($jobRequest));

        if ($notification = $this->notificationService->retrieveByCode(Notification::CODE_SUPPLIER_NEW_REQUEST)) {
            $link = $this->urlGenerator->generate('buyer-id', ['id' => $buyer->getId()], Router::ABSOLUTE_URL);
            $user = $supplier->getUserRelatedByUserId();

            $data = $this->dataObjectBuilder->build(NotificationUserData::class, [
                'user' => $user,
                'notification' => $notification,
                'link' => $link,
                'buyer' => $buyer,
                'supplier' => $supplier,
            ]);

            $userNotification = $this->notificationService->createUserNotification($data);

            $this->eventPublisher->publish(
                $user,
                $userNotification,
                [
                    EventPublisher::MESSAGE_TYPE => NotificationService::EVENT_MESSAGE_TYPE_NEW_NOTIFICATION
                ]
            );

            $this->notificationService->doDuplicateByEmail($supplier, $userNotification);
        }

        return $jobRequest;
    }
}
