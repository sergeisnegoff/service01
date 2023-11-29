<?php


namespace App\Service\Supplier\SupplierList;


use App\Model\Company;

class SupplierListContext
{
    protected ?Company $company;
    protected bool $favorite = false;
    protected bool $mySuppliers = false;
    protected string $query = '';

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;
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
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    /**
     * @param bool $favorite
     */
    public function setFavorite(bool $favorite): self
    {
        $this->favorite = $favorite;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMySuppliers(): bool
    {
        return $this->mySuppliers;
    }

    /**
     * @param bool $mySuppliers
     */
    public function setMySuppliers(bool $mySuppliers): self
    {
        $this->mySuppliers = $mySuppliers;
        return $this;
    }
}
