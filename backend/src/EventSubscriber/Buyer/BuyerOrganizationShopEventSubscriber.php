<?php


namespace App\EventSubscriber\Buyer;


use App\Service\Buyer\BuyerOrganizationService;
use App\Service\Buyer\Event\AfterCreateOrganizationShopEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuyerOrganizationShopEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var BuyerOrganizationService
     */
    private BuyerOrganizationService $buyerOrganizationService;

    public function __construct(BuyerOrganizationService $buyerOrganizationService)
    {
        $this->buyerOrganizationService = $buyerOrganizationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterCreateOrganizationShopEvent::class => 'onAfterCreateShop',
        ];
    }

    public function onAfterCreateShop(AfterCreateOrganizationShopEvent $event)
    {
        $this->buyerOrganizationService->sendNewShopNotification($event->getShop());
    }
}
