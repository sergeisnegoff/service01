<?php


namespace App\Service\Buyer\Event;


use App\Model\CompanyOrganizationShop;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCreateOrganizationShopEvent extends Event
{
    protected CompanyOrganizationShop $shop;

    /**
     * @return CompanyOrganizationShop
     */
    public function getShop(): CompanyOrganizationShop
    {
        return $this->shop;
    }

    /**
     * @param CompanyOrganizationShop $shop
     */
    public function setShop(CompanyOrganizationShop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }
}
