<?php

declare(strict_types=1);

namespace App\Service\Iiko;

use App\Helper\PriceFormatHelper;
use App\Model\IikoSetting;
use App\Model\Invoice;
use App\Model\InvoiceProduct;
use Exception;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class IikoInvoiceService
{
    private IikoClient $client;
    private LoggerInterface $logger;

    public function __construct(IikoClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    protected function buildAddData(Invoice $invoice): SimpleXMLElement
    {
        // https://ru.iiko.help/articles/#!api-documentations/loading-and-editing-the-delivery-note
        $xml = new SimpleXMLElement('<document/>');
        $xml->addChild('transportInvoiceNumber', sprintf('tin-%d', $invoice->getId()));
        $xml->addChild('employeePassToAccount', '');
        $xml->addChild('incomingDocumentNumber', sprintf('idn-%d', $invoice->getId()));

        if ($invoice->getPayUpTo()) {
            $xml->addChild('dueDate', $invoice->getPayUpTo(DATE_ATOM));
        }

        $xml->addChild('supplier', '');
        $xml->addChild('defaultStore', '');
        $xml->addChild('invoice', sprintf('in-%d', $invoice->getId()));
        $xml->addChild('useDefaultDocumentTime', 'true');
        $xml->addChild('dateIncoming', $invoice->getCreatedAt(DATE_ATOM));
        $xml->addChild('documentNumber', sprintf('dn-%d', $invoice->getId()));
        $xml->addChild('comment', $invoice->getComment());
        $xml->addChild('conception', '');

        $items = $xml->addChild('items');

        $invoiceProducts = $invoice->getInvoiceProducts();
        $warehouse = $invoice->getWarehouse();

        $i = 1;

        foreach ($invoiceProducts as $invoiceProduct) {
            /** @var InvoiceProduct $comparisonProduct */
            $comparisonProduct = $invoiceProduct->getInvoiceProductsRelatedById()->getFirst();

            if (!$comparisonProduct) {
                continue;
            }

            $item = $items->addChild('item');

            $item->addChild('amount', (string) $invoiceProduct->getQuantityWithComparisonRate());
            $item->addChild('supplierProduct', '');
            $item->addChild('product', $comparisonProduct->getProduct()->getExternalCode());
            $item->addChild('num', (string) $i);
            $item->addChild('containerId');
            $item->addChild('amountUnit', $comparisonProduct->getUnit()->getExternalCode());
            $item->addChild('actualUnitWeight');
            $item->addChild('discountSum', (string) 0);
            $item->addChild('sumWithoutNds', (string) PriceFormatHelper::format($comparisonProduct->getAcceptedPrice()));
            $item->addChild('ndsPercent', (string) $invoiceProduct->getActualVat());
            $item->addChild('sum', (string) PriceFormatHelper::format($comparisonProduct->getAcceptedPriceWithVat()));
            $item->addChild('priceUnit');
            $item->addChild('price', (string) PriceFormatHelper::format($comparisonProduct->getPriceWithVat()));
            $item->addChild('store', $warehouse ? $warehouse->getExternalCode() : '');
            $item->addChild('customsDeclarationNumber', sprintf('cdn-%d', $invoice->getId()));
            $item->addChild('actualAmount', (string) $invoiceProduct->getQuantityWithComparisonRate());

            $i++;
        }

        return $xml;
    }

    public function add(IikoSetting $setting, Invoice $invoice): string
    {
        $client = $this->client;
        $client->init($setting);

        try {
            $response = $client->addInvoice($this->buildAddData($invoice));
            $client->logout();

            if (!$response[0]['value']) {
                throw new Exception(print_r($response, true));
            }

            $invoice->setIikoSend(true)->save();

            return $response[2]['value'];

        } catch (Exception $exception) {
            $client->logout();

            $this->logger->error(sprintf('IIKO INVOICE ADD ERROR: [%s]', $exception->getMessage()));
            throw $exception;
        }
    }
}
