<?php


namespace App\Service\Invoice\InvoiceList;


use App\Model\Company;
use App\Model\CompanyOrganizationShop;

class InvoiceListContext
{
    protected ?Company $company = null;
    protected string $search = '';
    protected string $number = '';
    protected ?\DateTime $dateFrom = null;
    protected ?\DateTime $dateTo = null;
    protected ?\DateTime $dateChangeFrom = null;
    protected ?\DateTime $dateChangeTo = null;
    protected ?Company $relatedCompany = null;
    protected ?CompanyOrganizationShop $shop = null;
    protected ?float $priceTo = null;
    protected ?float $priceFrom = null;
    protected ?int $acceptanceStatusId = null;
    protected array $suppliersId = [];
    protected array $buyersId = [];
    protected array $invoicesId = [];
    protected string $sortField = '';
    protected string $sortDirection = '';

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     */
    public function setSearch(string $search): self
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortField(): string
    {
        return $this->sortField;
    }

    /**
     * @param string $sortField
     */
    public function setSortField(string $sortField): self
    {
        $this->sortField = $sortField;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * @param string $sortDirection
     */
    public function setSortDirection(string $sortDirection): self
    {
        $this->sortDirection = $sortDirection;
        return $this;
    }

    /**
     * @return array
     */
    public function getSuppliersId(): array
    {
        return $this->suppliersId;
    }

    /**
     * @param array $suppliersId
     */
    public function setSuppliersId(array $suppliersId): self
    {
        $this->suppliersId = $suppliersId;
        return $this;
    }

    /**
     * @return array
     */
    public function getBuyersId(): array
    {
        return $this->buyersId;
    }

    /**
     * @param array $buyersId
     */
    public function setBuyersId(array $buyersId): self
    {
        $this->buyersId = $buyersId;
        return $this;
    }

    /**
     * @return array
     */
    public function getInvoicesId(): array
    {
        return $this->invoicesId;
    }

    /**
     * @param array $invoicesId
     */
    public function setInvoicesId(array $invoicesId): self
    {
        $this->invoicesId = $invoicesId;
        return $this;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company|null $company
     */
    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime|null $dateFrom
     */
    public function setDateFrom(?\DateTime $dateFrom): self
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateChangeFrom(): ?\DateTime
    {
        return $this->dateChangeFrom;
    }

    /**
     * @param \DateTime|null $dateChangeFrom
     */
    public function setDateChangeFrom(?\DateTime $dateChangeFrom): self
    {
        $this->dateChangeFrom = $dateChangeFrom;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateChangeTo(): ?\DateTime
    {
        return $this->dateChangeTo;
    }

    /**
     * @param \DateTime|null $dateChangeTo
     */
    public function setDateChangeTo(?\DateTime $dateChangeTo): self
    {
        if ($dateChangeTo) {
            $dateChangeTo->setTime(23, 59, 59);
        }

        $this->dateChangeTo = $dateChangeTo;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    /**
     * @param \DateTime|null $dateTo
     */
    public function setDateTo(?\DateTime $dateTo): self
    {
        if ($dateTo) {
            $dateTo->setTime(23, 59, 59);
        }

        $this->dateTo = $dateTo;
        return $this;
    }

    /**
     * @return Company|null
     */
    public function getRelatedCompany(): ?Company
    {
        return $this->relatedCompany;
    }

    /**
     * @param Company|null $relatedCompany
     */
    public function setRelatedCompany(?Company $relatedCompany): self
    {
        $this->relatedCompany = $relatedCompany;
        return $this;
    }

    /**
     * @return CompanyOrganizationShop|null
     */
    public function getShop(): ?CompanyOrganizationShop
    {
        return $this->shop;
    }

    /**
     * @param CompanyOrganizationShop|null $shop
     */
    public function setShop(?CompanyOrganizationShop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPriceTo(): ?float
    {
        return $this->priceTo;
    }

    /**
     * @param float|null $priceTo
     */
    public function setPriceTo(?float $priceTo): self
    {
        $this->priceTo = $priceTo;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPriceFrom(): ?float
    {
        return $this->priceFrom;
    }

    /**
     * @param float|null $priceFrom
     */
    public function setPriceFrom(?float $priceFrom): self
    {
        $this->priceFrom = $priceFrom;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAcceptanceStatusId(): ?int
    {
        return $this->acceptanceStatusId;
    }

    public function setAcceptanceStatusId(?int $acceptanceStatusId): self
    {
        $this->acceptanceStatusId = $acceptanceStatusId;
        return $this;
    }

    public function getNormalizeSort(): array
    {
        return [
            'field' => $this->sortField ?? 'CreatedAt',
            'direction' => $this->sortDirection ?? 'DESC',
        ];
    }
}
