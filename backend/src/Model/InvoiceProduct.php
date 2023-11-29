<?php

namespace App\Model;

use App\Model\Base\InvoiceProduct as BaseInvoiceProduct;

/**
 * Skeleton subclass for representing a row from the 'invoice_product' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class InvoiceProduct extends BaseInvoiceProduct
{
    const DEFAULT_COMPARISON_RATE = 1;

    public function getTotalPrice()
    {
        return $this->total_price ?: $this->price * $this->getQuantityWithComparisonRate();
    }

    public function getActualVat()
    {
        if (!$this->invoice_id) {
            $comparisonProduct = $this->getInvoiceProductRelatedByComparisonId();
            $product = $comparisonProduct->getProduct();

        } else {
            $product = $this->getProduct();
            $comparisonProduct = $this;
        }

        return $comparisonProduct->getVat() ?: ($product ? $product->getVat() : 0);
    }

    public function getTotalPriceWithVat()
    {
        if (parent::getTotalPriceWithVat()) {
            return parent::getTotalPriceWithVat();
        }

        $totalPrice = $this->getTotalPrice();
        $vat = $this->getActualVat();

        return $vat ? $totalPrice + ($totalPrice / 100 * $vat) : $totalPrice;
    }

    public function getPriceWithVat()
    {
        if (parent::getPriceWithVat()) {
            return parent::getPriceWithVat();
        }

        $vat = $this->getActualVat();
        $price = $this->price;

        return $vat ? $price + ($price / 100 * $vat) : $price;
    }

    public function getAcceptedPrice()
    {
        return $this->getPrice() * $this->getQuantityWithComparisonRate();
    }

    public function getAcceptedPriceWithVat()
    {
        return $this->getPriceWithVat() * $this->getQuantityWithComparisonRate();
    }

    public function getComparisonRate()
    {
        return parent::getComparisonRate() ?: self::DEFAULT_COMPARISON_RATE;
    }

    public function getQuantityWithComparisonRate()
    {
        if (!$this->quantity_rate && !$this->comparison_rate) {
            return $this->quantity;
        }

        return $this->quantity_rate ?: $this->quantity * $this->comparison_rate;
    }
}
