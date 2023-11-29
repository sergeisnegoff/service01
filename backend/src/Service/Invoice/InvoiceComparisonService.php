<?php


namespace App\Service\Invoice;


use App\EventPublisher\EventPublisher;
use App\Model\Invoice;
use App\Model\InvoiceProduct;
use App\Model\InvoiceProductQuery;
use App\Model\InvoiceStatus;
use App\Model\Notification;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\Invoice\InvoiceComparisonData\InvoiceComparisonData;
use App\Service\Invoice\InvoiceComparisonData\InvoiceComparisonProductData;
use App\Service\Notification\NotificationService;
use App\Service\Notification\NotificationUserData;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class InvoiceComparisonService
{
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;
    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;
    private DataObjectBuilder $dataObjectBuilder;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        NotificationService $notificationService,
        EventPublisher $eventPublisher,
        DataObjectBuilder $dataObjectBuilder,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->notificationService = $notificationService;
        $this->eventPublisher = $eventPublisher;
        $this->dataObjectBuilder = $dataObjectBuilder;
        $this->urlGenerator = $urlGenerator;
    }

    public function processComparisonInvoice(Invoice $invoice, InvoiceComparisonData $comparisonData): Invoice
    {
        $this->comparisonInvoiceProducts($comparisonData->getProducts());

        $invoice
            ->setCounterparty($comparisonData->getCounterparty() ?: $invoice->getCounterparty())
            ->setComment($comparisonData->getComment() ?: $invoice->getComment())
            ->setMessageSupplier($comparisonData->getMessageSupplier() ?: $invoice->getMessageSupplier())
        ;

        if ($comparisonData->getWarehouse()) {
            $invoice->setWarehouse($comparisonData->getWarehouse());
        }

        if ($comparisonData->getPayUpTo()) {
            $invoice->setPayUpTo($comparisonData->getPayUpTo());
        }

        if ($comparisonData->isCancel()) {
            $invoice->setInvoiceStatusRelatedByAcceptanceStatusId(InvoiceStatus::retrieveByCode(InvoiceStatus::CODE_NOT_ACCEPTED));
            $this->resetComparisonProducts($invoice);
            $this->sendStatusNotification($invoice, Notification::CODE_INVOICE_CANCEL);

        } else if ($comparisonData->isDischarge()) {
            $invoice->setInvoiceStatusRelatedByDischargeStatusId(InvoiceStatus::retrieveByCode(InvoiceStatus::CODE_DISCHARGE));
        }

        $invoice->save();

        return $invoice;
    }

    /**
     * @param InvoiceComparisonProductData[] $products
     */
    public function comparisonInvoiceProducts($products)
    {
        foreach ($products as $product) {
            if (!$invoiceProduct = $product->getInvoiceProduct()) {
                continue;
            }

            if (!$product->getProduct()) {
                continue;
            }

            $comparisonProduct = $this->getInvoiceComparisonProduct($product->getInvoiceProduct()) ?: (new InvoiceProduct())->setInvoiceProductRelatedByComparisonId($product->getInvoiceProduct());

            $comparisonProduct
                ->setPrice($invoiceProduct->getPrice())
                ->setProduct($product->getProduct())
                ->setUnit($product->getUnit() ?: $product->getProduct()->getUnit())
                ->setComparisonRate($product->getComparisonRate())
                ->setQuantityRate($product->getQuantityRate())
                ->setQuantity($product->getQuantity())
                ->setAcceptQuantity($product->getQuantity())
                ->save();
        }
    }

    public function resetComparisonProducts(Invoice $invoice)
    {
        $products = InvoiceProductQuery::create()
            ->distinct()
            ->useInvoiceProductRelatedByComparisonIdQuery('root')
                ->filterByInvoice($invoice)
            ->endUse()
            ->find();

        foreach ($products as $product) {
            $product->setQuantity(0)->save();
        }
    }

    protected function getInvoiceComparisonProduct(InvoiceProduct $product)
    {
        return InvoiceProductQuery::create()
            ->filterByInvoiceProductRelatedByComparisonId($product)
            ->findOne();
    }

    public function sendStatusNotification(Invoice $invoice, string $notificationCode)
    {
        if (!$notification = $this->notificationService->retrieveByCode($notificationCode)) {
            return;
        }

        $company = $invoice->getCompanyRelatedBySupplierId();
        $buyer = $invoice->getCompanyRelatedByBuyerId();
        $users = $company->getCompanyUsersData();

        $text = strtr($notification->getText(), [
            '#buyerTitle#' => $buyer->getTitle(),
            '#buyerInn#' => $buyer->getInn(),
            '#number#' => $invoice->getId(),
            '#status#' => $invoice->getInvoiceStatusRelatedByAcceptanceStatusId()->getTitle(),
        ]);

        foreach ($users as $user) {
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
            $this->notificationService->doDuplicateByEmail($company, $userNotification, $notificationCode);
        }
    }
}
