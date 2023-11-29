<?php


namespace App\Service\Invoice\InvoiceAcceptData;


use App\Model\Counterparty;
use App\Model\CounterpartyQuery;
use App\Model\InvoiceProductQuery;
use App\Service\DataObject\DataObjectInterface;
use App\Service\Invoice\InvoiceComparisonData\InvoiceComparisonProductData;

class InvoiceAcceptData implements DataObjectInterface
{
    protected ?int $acceptanceStatusId = null;
    protected ?int $dischargeStatusId = null;
    protected ?int $egaisStatus = null;
    protected $acceptanceAt = null;
    protected string $commentEgais = '';
    protected string $comment = '';
    protected ?Counterparty $counterparty = null;
    protected ?int $counterpartyId = null;
    protected string $messageSupplier = '';
    protected string $hasAccepted = '';
    protected string $linkOrder = '';
    protected bool $cancel = false;
    protected bool $save = false;

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

    public function setCounterparty()
    {
        if ($this->counterpartyId) {
            $this->counterparty = CounterpartyQuery::create()->findPk($this->counterpartyId);
        }
    }

    /**
     * @return bool
     */
    public function isSave(): bool
    {
        return $this->save;
    }

    /**
     * @param bool $save
     */
    public function setSave(bool $save): self
    {
        $this->save = $save;
        return $this;
    }

    /** @var InvoiceComparisonProductData[] */
    protected array $products = [];

    /**
     * @return string
     */
    public function getLinkOrder(): string
    {
        return $this->linkOrder;
    }

    /**
     * @param string $linkOrder
     */
    public function setLinkOrder(string $linkOrder): self
    {
        $this->linkOrder = $linkOrder;
        return $this;
    }

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

    /**
     * @return int|null
     */
    public function getEgaisStatus(): ?int
    {
        return $this->egaisStatus;
    }

    /**
     * @param int|null $egaisStatus
     */
    public function setEgaisStatus(?int $egaisStatus): self
    {
        $this->egaisStatus = $egaisStatus;
        return $this;
    }

    /**
     * @return null
     */
    public function getAcceptanceAt()
    {
        return $this->acceptanceAt;
    }

    /**
     * @param null $acceptanceAt
     */
    public function setAcceptanceAt($acceptanceAt): self
    {
        if (strtotime($acceptanceAt)) {
            $acceptanceAt = new \DateTime($acceptanceAt);
        }

        $this->acceptanceAt = $acceptanceAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommentEgais(): string
    {
        return $this->commentEgais;
    }

    /**
     * @param string $commentEgais
     */
    public function setCommentEgais(string $commentEgais): self
    {
        $this->commentEgais = $commentEgais;
        return $this;
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
     * @return string
     */
    public function getHasAccepted(): string
    {
        return $this->hasAccepted;
    }

    /**
     * @param string $hasAccepted
     */
    public function setHasAccepted(string $hasAccepted): self
    {
        $this->hasAccepted = $hasAccepted;
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
     * @return InvoiceComparisonProductData[]
     */
    public function getProducts(): array
    {
        return $this->products;
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

    public function initProducts()
    {
        $products = $this->getProducts();
        $this->products = [];

        foreach ($products as $product) {
            $invoiceProduct = new InvoiceComparisonProductData();
            $invoiceProduct
                ->setInvoiceProduct(InvoiceProductQuery::create()->findPk($product['invoiceProductId'] ?? 0))
                ->setQuantity((float) ($product['quantity'] ?? 0));

            $this->products[] = $invoiceProduct;
        }
    }
}
