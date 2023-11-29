<?php


namespace App\Service\Company\Event;


use App\Model\CompanyUser;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCreateCompanyUserEvent extends Event
{
    protected CompanyUser $companyUser;

    /**
     * @return CompanyUser
     */
    public function getCompanyUser(): CompanyUser
    {
        return $this->companyUser;
    }

    /**
     * @param CompanyUser $companyUser
     */
    public function setCompanyUser(CompanyUser $companyUser): self
    {
        $this->companyUser = $companyUser;
        return $this;
    }
}
