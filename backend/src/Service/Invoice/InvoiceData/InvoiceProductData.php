<?php


namespace App\Service\Invoice\InvoiceData;


use App\Model\InvoiceProduct;
use App\Model\Product;
use App\Model\Unit;

class InvoiceProductData
{
    protected ?InvoiceProduct $invoiceProduct = null;

    protected ?Product $product = null;
    protected ?Unit $unit = null;
    protected ?float $price = 0;
    protected ?float $priceWithVat = 0;
    protected ?float $totalPrice = 0;
    protected ?float $totalPriceVat = 0;
    protected ?float $totalPriceWithVat = 0;
    protected ?float $quantity = 0;
    protected ?int $vat = 0;

    /**
     * @return float|null
     */
    public function getPriceWithVat(): ?float
    {
        return $this->priceWithVat;
    }

    /**
     * @param float|null $priceWithVat
     */
    public function setPriceWithVat(?float $priceWithVat): self
    {
        $this->priceWithVat = $priceWithVat;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalPriceVat(): ?float
    {
        return $this->totalPriceVat;
    }

    /**
     * @param float|null $totalPriceVat
     */
    public function setTotalPriceVat(?float $totalPriceVat): self
    {
        $this->totalPriceVat = $totalPriceVat;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalPriceWithVat(): ?float
    {
        return $this->totalPriceWithVat;
    }

    /**
     * @param float|null $totalPriceWithVat
     */
    public function setTotalPriceWithVat(?float $totalPriceWithVat): self
    {
        $this->totalPriceWithVat = $totalPriceWithVat;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getVat(): ?int
    {
        return $this->vat;
    }

    /**
     * @param int|null $vat
     */
    public function setVat(?int $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

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
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): self
    {
        $this->price = $price;
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
     * @return float|int|null
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @param float|int|null $totalPrice
     *
     * @return InvoiceProductData
     */
    public function setTotalPrice($totalPrice): InvoiceProductData
    {
        $this->totalPrice = $totalPrice;
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
     *
     * @return InvoiceProductData
     */
    public function setUnit(?Unit $unit): InvoiceProductData
    {
        $this->unit = $unit;
        return $this;
    }
}
