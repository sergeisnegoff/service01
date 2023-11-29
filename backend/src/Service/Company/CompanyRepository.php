<?php


namespace App\Service\Company;


use App\Model\Company;
use App\Model\CompanyQuery;

class CompanyRepository
{
    public function findPk($id): ?Company
    {
        return CompanyQuery::create()->findPk($id);
    }
}
