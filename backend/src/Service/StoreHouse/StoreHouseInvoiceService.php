<?php

declare(strict_types=1);

namespace App\Service\StoreHouse;

use App\Model\Invoice;
use App\Model\InvoiceProduct;
use App\Model\StoreHouseSetting;
use Exception;
use Psr\Log\LoggerInterface;

class StoreHouseInvoiceService
{
    public const
        OPTIONS_RID = 1,
        CURRENCY_RATE = 1,
        SUPPLIER_RID = 0,
        CURRENCY_RID = 0,
        UNIT_RID = 0,
        NSP = 0,
        NSP_PRICE = 0
    ;

    private StoreHouseClient $storeHouseClient;
    private LoggerInterface $logger;
    private StoreHouseImportService $importService;

    public function __construct(
        StoreHouseClient $storeHouseClient,
        LoggerInterface $logger,
        StoreHouseImportService $importService
    ) {
        $this->storeHouseClient = $storeHouseClient;
        $this->logger = $logger;
        $this->importService = $importService;
    }

    public function add(StoreHouseSetting $setting, Invoice $invoice): void
    {
        $this->storeHouseClient->init($setting);

        try {
            $response = $this->storeHouseClient->addInvoice($this->getInvoiceData($setting, $invoice));

            if (isset($response['errMessage']) && $response['errMessage'] !== 'OK') {
                throw new Exception($response['errMessage']);
            }

            $invoice
                ->setExternalCode($this->importService->normalizeGuid($response['shTable'][0]['values'][0][0]))
                ->setStoreHouseSend(true)
                ->save();

        } catch (Exception $exception) {
            $this->logger->error(sprintf('STORE HOUSE ADD INVOICE ERROR: [%s]', $exception->getMessage()));
            throw $exception;
        }
    }

    private function getInvoiceData(StoreHouseSetting $setting, Invoice $invoice): array
    {
        $invoiceDataSet = $this->getInvoiceDataSet();
        $invoiceProductDataSet = $this->getInvoiceProductDataSet();

        $data = [
            [
                'head' => AbstractStoreHouseClient::TABLE_INVOICE,
                'original' => array_keys($invoiceDataSet),
                'values' => [],
            ],
            [
                'head' => AbstractStoreHouseClient::TABLE_INVOICE_PRODUCT,
                'original' => array_keys($invoiceProductDataSet),
                'values' => [],
            ]
        ];

        foreach ($invoiceDataSet as $function) {
            $data[0]['values'][] = [call_user_func($function, $invoice, $setting)];
        }

        $invoiceProducts = $invoice->getInvoiceProducts();
        $i = 0;

        foreach ($invoiceProductDataSet as $function) {
            foreach ($invoiceProducts as $invoiceProduct) {
                $comparisonProduct = $invoiceProduct->getInvoiceProductsRelatedById()->getFirst();

                if (!$comparisonProduct) {
                    continue;
                }

                if (array_key_exists($i, $data[1]['values'])) {
                    array_push($data[1]['values'][$i], call_user_func($function, $comparisonProduct));

                } else {
                    $data[1]['values'][$i] = [call_user_func($function, $comparisonProduct)];
                }
            }

            $i++;
        }

        return $data;
    }

    private function getInvoiceDataSet(): array
    {
        return [
            '33' => [$this, 'getInvoiceOptionRid'],
            '31' => [$this, 'getInvoiceDate'],
            '100\\1' => [$this, 'getCurrency'],
            '34' => [$this, 'getCurrencyRate'],
            '35' => [$this, 'getCurrencyRate'],
            '105\\1' => [$this, 'getSupplierRid'],
            '105#1\\1' => [$this, 'getBuyerRid'],
        ];
    }

    private function getInvoiceProductDataSet(): array
    {
        return [
            '210\\1' => [$this, 'getProductRid'],
            '210\\206\\1' => [$this, 'getProductUnitRid'],
            '212\\9' => [$this, 'getProductVat'],
            '213\\9' => [$this, 'getProductNsp'],
            '40' => [$this, 'getProductPrice'],
            '41' => [$this, 'getProductVatPrice'],
            '42' => [$this, 'getProductNpsPrice'],
            '32' => [$this, 'getProductOptionRid'],
            '31' => [$this, 'getProductQuantity'],
        ];
    }

    private function getInvoiceOptionRid(Invoice $invoice, StoreHouseSetting $setting): int
    {
        return self::OPTIONS_RID;
    }

    private function getCurrency(Invoice $invoice, StoreHouseSetting $setting): int
    {
        $currencies = $this->importService->normalizeData(AbstractStoreHouseClient::TABLE_CURRENCIES, $this->storeHouseClient->getCurrencies());
        $currencies = array_filter($currencies, fn($currency) => $currency['Name'] === 'Рубль');

        $currency = array_shift($currencies);

        return $currency['Rid'] ?? self::CURRENCY_RID;
    }

    private function getCurrencyRate(Invoice $invoice, StoreHouseSetting $setting): int
    {
        return self::CURRENCY_RATE;
    }

    private function getSupplierRid(Invoice $invoice, StoreHouseSetting $setting): int
    {
        if ($supplier = $invoice->getCompanyRelatedBySupplierId()) {
            return (int) $supplier->getStorehouseExternalCode() ?? self::SUPPLIER_RID;
        }

        return self::SUPPLIER_RID;
    }

    private function getBuyerRid(Invoice $invoice, StoreHouseSetting $setting): int
    {
        if ($invoice->getWarehouseId()) {
            return (int) $invoice->getWarehouse()->getExternalCode();

        } else if ($setting->getWarehouseId()) {
            return (int) $setting->getWarehouse()->getExternalCode();
        }

        return (int) $setting->getRid();
    }

    private function getInvoiceDate(Invoice $invoice, StoreHouseSetting $setting): string
    {
        return $invoice->getCreatedAt('Y-m-d H:i:s');
    }

    private function getProductRid(InvoiceProduct $product): int
    {
        return (int) $product->getProduct()->getExternalCode();
    }

    private function getProductUnitRid(InvoiceProduct $product): int
    {
        $options = @json_decode($product->getProduct()->getOptions(), true);

        return $options['Ei: Rid'] ?? self::UNIT_RID;
    }

    private function getProductVat(InvoiceProduct $product)
    {
        $product = $product->getInvoiceProductRelatedByComparisonId();
        return $product->getActualVat() * 100;
    }

    private function getProductNsp(InvoiceProduct $product): int
    {
        return self::NSP;
    }

    private function getProductPrice(InvoiceProduct $product): float
    {
        return $product->getAcceptedPrice();
    }

    private function getProductVatPrice(InvoiceProduct $product)
    {
        return $product->getAcceptedPriceWithVat() - $product->getAcceptedPrice();
    }

    private function getProductNpsPrice(InvoiceProduct $product): int
    {
        return self::NSP_PRICE;
    }

    private function getProductOptionRid(InvoiceProduct $product): int
    {
        return self::OPTIONS_RID;
    }

    private function getProductQuantity(InvoiceProduct $product): float
    {
        $product = $product->getInvoiceProductRelatedByComparisonId();
        return $product->getQuantityWithComparisonRate();
    }
}
