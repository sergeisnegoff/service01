<?php

namespace App\Service\Invoice\InvoiceComparisonData;

use App\Model\InvoiceProduct;
use App\Model\Product;
use App\Model\Unit;
use App\Model\Warehouse;

class InvoiceComparisonProductData
{
    protected ?InvoiceProduct $invoiceProduct = null;

    protected ?Product $product = null;
    protected ?Unit $unit = null;
    protected ?float $quantity = 0;
    protected ?float $comparisonRate = 0;
    protected ?float $quantityRate = 0;

    /**
     * @return InvoiceProduct|null
     */
    public function getInvoiceProduct(): ?InvoiceProduct
    {
        return $this->invoiceProduct;
    }

    /**
     * @param InvoiceProduct|null $invoiceProduct
     */
    public function setInvoiceProduct(?InvoiceProduct $invoiceProduct): self
    {
        $this->invoiceProduct = $invoiceProduct;
        return $this;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     */
    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    /**
     * @param Unit|null $unit
     */
    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    /**
     * @param float|null $quantity
     */
    public function setQuantity(?float $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getComparisonRate(): ?float
    {
        return $this->comparisonRate;
    }

    /**
     * @param float|null $comparisonRate
     */
    public function setComparisonRate(?float $comparisonRate): self
    {
        $this->comparisonRate = $comparisonRate;
        return $this;
    }

    /**
     * @return float|int|null
     */
    public function getQuantityRate()
    {
        return $this->quantityRate;
    }

    /**
     * @param float|int|null $quantityRate
     *
     * @return InvoiceComparisonProductData
     */
    public function setQuantityRate($quantityRate): InvoiceComparisonProductData
    {
        $this->quantityRate = $quantityRate;
        return $this;
}
}
