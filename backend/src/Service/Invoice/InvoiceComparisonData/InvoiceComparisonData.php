<?php

namespace App\Service\Invoice\InvoiceComparisonData;

use App\Model\Counterparty;
use App\Model\CounterpartyQuery;
use App\Model\InvoiceProductQuery;
use App\Model\ProductQuery;
use App\Model\UnitQuery;
use App\Model\Warehouse;
use App\Model\WarehouseQuery;
use App\Service\DataObject\DataObjectInterface;

class InvoiceComparisonData implements DataObjectInterface
{
    protected ?int $acceptanceStatusId = null;
    protected ?int $dischargeStatusId = null;
    protected $payUpTo = null;
    protected string $comment = '';
    protected ?Counterparty $counterparty = null;
    protected ?Warehouse $warehouse = null;
    protected ?int $counterpartyId = null;
    protected ?int $warehouseId = null;
    protected string $messageSupplier = '';
    protected bool $discharge = false;
    protected bool $cancel = false;

    /**
     * @return int|null
     */
    public function getWarehouseId(): ?int
    {
        return $this->warehouseId;
    }

    /**
     * @param int|null $warehouseId
     */
    public function setWarehouseId(?int $warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }

    /**
     * @return string
     */
    public function getMessageSupplier(): string
    {
        return $this->messageSupplier;
    }

    /**
     * @param string $messageSupplier
     */
    public function setMessageSupplier(string $messageSupplier): self
    {
        $this->messageSupplier = $messageSupplier;
        return $this;
    }

    /**
     * @return Counterparty|null
     */
    public function getCounterparty(): ?Counterparty
    {
        return $this->counterparty;
    }

    /**
     * @return int|null
     */
    public function getCounterpartyId(): ?int
    {
        return $this->counterpartyId;
    }

    /**
     * @param int|null $counterpartyId
     */
    public function setCounterpartyId(?int $counterpartyId): self
    {
        $this->counterpartyId = $counterpartyId;
        return $this;
    }

    /** @var InvoiceComparisonProductData[] */
    protected array $products = [];

    /**
     * @return int|null
     */
    public function getAcceptanceStatusId(): ?int
    {
        return $this->acceptanceStatusId;
    }

    /**
     * @param int|null $acceptanceStatusId
     */
    public function setAcceptanceStatusId(?int $acceptanceStatusId): self
    {
        $this->acceptanceStatusId = $acceptanceStatusId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDischargeStatusId(): ?int
    {
        return $this->dischargeStatusId;
    }

    /**
     * @param int|null $dischargeStatusId
     */
    public function setDischargeStatusId(?int $dischargeStatusId): self
    {
        $this->dischargeStatusId = $dischargeStatusId;
        return $this;
    }

    public function getPayUpTo()
    {
        return $this->payUpTo;
    }

    /**
     * @param null $payUpTo
     */
    public function setPayUpTo($payUpTo): self
    {
        if (strtotime($payUpTo)) {
            $payUpTo = new \DateTime($payUpTo);
        }

        $this->payUpTo = $payUpTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDischarge(): bool
    {
        return $this->discharge;
    }

    /**
     * @param bool $discharge
     */
    public function setDischarge(bool $discharge): self
    {
        $this->discharge = $discharge;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCancel(): bool
    {
        return $this->cancel;
    }

    /**
     * @param bool $cancel
     */
    public function setCancel(bool $cancel): self
    {
        $this->cancel = $cancel;
        return $this;
    }

    /**
     * @param InvoiceComparisonProductData[] $products
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;
        $this->initProducts();
        return $this;
    }

    /**
     * @return InvoiceComparisonProductData[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    public function initProducts()
    {
        $products = $this->getProducts();
        $this->products = [];

        foreach ($products as $product) {
            $invoiceProduct = new InvoiceComparisonProductData();

            if ($product['invoiceProductId'] ?? null) {
                $invoiceProduct->setInvoiceProduct(InvoiceProductQuery::create()->findPk($product['invoiceProductId']));
            }

            if ($product['productId'] ?? null) {
                $invoiceProduct->setProduct(ProductQuery::create()->findPk($product['productId']));
            }

            if ($product['unitId'] ?? null) {
                $invoiceProduct->setUnit(UnitQuery::create()->findPk($product['unitId']));
            }

            if ((float)($product['quantity'] ?? 0)) {
                $invoiceProduct->setQuantity((float)($product['quantity']));
            }

            if ((float)($product['quantityFact'] ?? 0)) {
                $invoiceProduct->setQuantityRate((float)($product['quantityFact']));
            }

            if ((float)($product['comparisonRate'] ?? 0)) {
                $invoiceProduct->setComparisonRate((float)($product['comparisonRate']));
            }

            $this->products[] = $invoiceProduct;
        }
    }

    public function setCounterparty()
    {
        if ($this->counterpartyId) {
            $this->counterparty = CounterpartyQuery::create()->findPk($this->counterpartyId);
        }

        return $this;
    }

    public function setWarehouse()
    {
        if ($this->warehouseId) {
            $this->warehouse = WarehouseQuery::create()->findPk($this->warehouseId);
        }

        return $this;
    }

    /**
     * @return Warehouse|null
     */
    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }
}
