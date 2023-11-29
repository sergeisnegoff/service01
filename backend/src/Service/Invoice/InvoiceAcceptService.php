<?php


namespace App\Service\Invoice;


use App\EventPublisher\EventPublisher;
use App\Model\Invoice;
use App\Model\InvoiceProduct;
use App\Model\InvoiceProductQuery;
use App\Model\InvoiceStatus;
use App\Model\Notification;
use App\Service\Invoice\InvoiceAcceptData\InvoiceAcceptData;
use App\Service\Invoice\InvoiceComparisonData\InvoiceComparisonProductData;
use App\Service\Notification\NotificationService;

class InvoiceAcceptService
{
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;
    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;
    /**
     * @var InvoiceComparisonService
     */
    private InvoiceComparisonService $comparisonService;
    /**
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    public function __construct(
        NotificationService $notificationService,
        EventPublisher $eventPublisher,
        InvoiceComparisonService $comparisonService,
        InvoiceService $invoiceService
    ) {
        $this->notificationService = $notificationService;
        $this->eventPublisher = $eventPublisher;
        $this->comparisonService = $comparisonService;
        $this->invoiceService = $invoiceService;
    }

    public function processAcceptInvoice(Invoice $invoice, InvoiceAcceptData $acceptData): Invoice
    {
        $this->acceptInvoiceProducts($acceptData->getProducts());

        $invoice
            ->setAcceptanceStatusId($acceptData->getAcceptanceStatusId())
            ->setCommentEgais($acceptData->getCommentEgais())
            ->setHasAccepted($acceptData->getHasAccepted())
            ->setLinkOrder($acceptData->getLinkOrder())
        ;

        if (!$acceptData->isSave()) {
            if ($acceptData->isCancel()) {
                $invoice->setInvoiceStatusRelatedByAcceptanceStatusId(InvoiceStatus::retrieveByCode(InvoiceStatus::CODE_CANCELED));
                $this->comparisonService->resetComparisonProducts($invoice);
                $notificationCode = Notification::CODE_INVOICE_CANCEL;

            } else {
                if ($this->invoiceService->isFullComparison($invoice)) {
                    $invoice->setInvoiceStatusRelatedByAcceptanceStatusId(InvoiceStatus::retrieveByCode(InvoiceStatus::CODE_ACCEPT));
                    $notificationCode = Notification::CODE_INVOICE_ACCEPT;

                } else {
                    $invoice->setInvoiceStatusRelatedByAcceptanceStatusId(InvoiceStatus::retrieveByCode(InvoiceStatus::CODE_ACCEPT_PARTIALLY));
                    $notificationCode = Notification::CODE_INVOICE_NOT_COMPLETELY;
                }
            }

            $this->comparisonService->sendStatusNotification($invoice, $notificationCode);
        }

        if (
            !$acceptData->getAcceptanceAt() &&
            $invoice->isAcceptanceStatus(InvoiceStatus::CODE_ACCEPT) ||
            $invoice->isAcceptanceStatus(InvoiceStatus::CODE_ACCEPT_PARTIALLY)
        ) {
            $acceptData->setAcceptanceAt(date(DATE_ATOM));
        }

        if ($acceptData->getAcceptanceAt()) {
            $invoice->setAcceptanceAt($acceptData->getAcceptanceAt());
        }

        $invoice->save();

        return $invoice;
    }

    /**
     * @param Invoice $invoice
     * @param InvoiceComparisonProductData[] $products
     */
    public function acceptInvoiceProducts($products)
    {
        foreach ($products as $product) {
            $invoiceProduct = $product->getInvoiceProduct();

            if (!$invoiceProduct) {
                continue;
            }

            $productObject = $invoiceProduct->getProduct();

            $comparisonProduct = $this->getInvoiceComparisonProduct($invoiceProduct) ?:
                (new InvoiceProduct())
                    ->setInvoiceProductRelatedByComparisonId($invoiceProduct)
                    ->setProduct($productObject)
                    ->setPrice($invoiceProduct->getPrice())
                    ->setUnit($invoiceProduct->getUnit());

            $comparisonProduct
                ->setQuantity($product->getQuantity())
                ->setAcceptQuantity($product->getQuantity())
                ->save();
        }
    }

    protected function getInvoiceComparisonProduct(InvoiceProduct $product)
    {
        return InvoiceProductQuery::create()
            ->filterByInvoiceProductRelatedByComparisonId($product)
            ->findOne();
    }
}
