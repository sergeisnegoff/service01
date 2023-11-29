<?php


namespace App\Service\Company;


use App\Model\Company;

class ActiveCompanyStorage
{
    protected ?Company $company;

    /**
     * @param Company|null $company
     */
    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }
}
