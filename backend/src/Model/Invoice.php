<?php

namespace App\Model;

use App\Model\Base\Invoice as BaseInvoice;

/**
 * Skeleton subclass for representing a row from the 'invoice' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Invoice extends BaseInvoice
{
    public static array $vatVariants = [10, 20];

    public static function getStringVatVariants(): array
    {
        return array_map(fn ($vat) => (string) $vat, self::$vatVariants);
    }

    public function getTotalPrice()
    {
        $totalPrice = InvoiceProductQuery::create()
            ->filterByInvoice($this)
            ->select('InvoiceProduct.total_price')
            ->findOne() ?: 0;

        if (!$totalPrice) {
            $totalPrice = InvoiceProductQuery::create()
                ->filterByInvoice($this)
                ->withColumn('SUM(InvoiceProduct.price * InvoiceProduct.quantity)', 'price')
                ->select('price')
                ->findOne() ?: 0;
        }

        return $totalPrice;
    }

    public function getTotalPriceWithVat()
    {
        $products = $this->getInvoiceProducts();

        $data = [];

        foreach ($products as $product) {
            $data[] = $product->getTotalPriceWithVat();
        }

        return array_sum($data);
    }

    public function getAcceptedTotalPrice()
    {
        $acceptedStatus = $this->getInvoiceStatusRelatedByAcceptanceStatusId();

        if (!$acceptedStatus || !in_array($acceptedStatus->getCode(), [InvoiceStatus::CODE_ACCEPT, InvoiceStatus::CODE_ACCEPT_PARTIALLY])) {
            return 0;
        }

        $invoiceProducts = $this->getInvoiceProducts();

        $sum = [];

        foreach ($invoiceProducts as $invoiceProduct) {
            /** @var InvoiceProduct $comparisonProduct */
            $comparisonProduct = $invoiceProduct->getInvoiceProductsRelatedById()->getFirst();

            if (!$comparisonProduct) {
                continue;
            }

            $sum[] = $comparisonProduct->getAcceptedPriceWithVat();
        }

        return array_sum($sum);
    }

    public function isOwner(Company $company): bool
    {
        return $this->getCompanyRelatedBySupplierId() === $company ||
            $this->getCompanyRelatedByBuyerId() === $company;
    }

    public function isAccepted(): bool
    {
        $acceptanceStatus = $this->getInvoiceStatusRelatedByAcceptanceStatusId();
        $dischargeStatus = $this->getInvoiceStatusRelatedByDischargeStatusId();

        return $acceptanceStatus && $acceptanceStatus->isAccept() &&
            $dischargeStatus && $dischargeStatus->isDischarge();
    }

    public function isAcceptanceStatus(string $code): bool
    {
        $acceptanceStatus = $this->getInvoiceStatusRelatedByAcceptanceStatusId();

        return $acceptanceStatus && $acceptanceStatus->getCode() === $code;
    }
}
