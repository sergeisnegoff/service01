<?php


namespace App\Service\Buyer\BuyerList;


use App\Model\Company;

class BuyerListContext
{
    protected ?Company $company;
    protected bool $favorite = false;
    protected bool $myBuyers = false;
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
    public function isMyBuyers(): bool
    {
        return $this->myBuyers;
    }

    /**
     * @param bool $myBuyers
     */
    public function setMyBuyers(bool $myBuyers): self
    {
        $this->myBuyers = $myBuyers;
        return $this;
    }
}
