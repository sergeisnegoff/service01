<?php


namespace App\Service\Product\ProductList;


use App\Model\Company;

class ProductListContext
{
    protected Company $company;
    protected array $categoriesId = [];
    protected string $search = '';
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
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategoriesId(): array
    {
        return $this->categoriesId;
    }

    /**
     * @param array $categoriesId
     */
    public function setCategoriesId(array $categoriesId): self
    {
        $this->categoriesId = $categoriesId;
        return $this;
    }
}
