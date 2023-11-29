<?php

namespace App\Service\Company\Event;

use App\Model\Company;
use App\Model\CompanyUser;
use App\Service\Company\CompanyUserData\CompanyUserData;
use Symfony\Contracts\EventDispatcher\Event;

class InviteCompanyUserEvent extends Event
{
    protected CompanyUser $companyUser;
    protected CompanyUserData $userData;
    protected Company $company;

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

    /**
     * @return CompanyUserData
     */
    public function getUserData(): CompanyUserData
    {
        return $this->userData;
    }

    /**
     * @param CompanyUserData $userData
     */
    public function setUserData(CompanyUserData $userData): self
    {
        $this->userData = $userData;
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
     *
     * @return InviteCompanyUserEvent
     */
    public function setCompany(Company $company): InviteCompanyUserEvent
    {
        $this->company = $company;
        return $this;
    }
}
