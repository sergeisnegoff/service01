<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\Model\Company;
use App\Model\CompanyUser;
use App\Model\CompanyUserQuery;

class CompanyUserRepository
{
    public function getCompanyUserByInviteData(?Company $company, string $phone): ?CompanyUser
    {
        return CompanyUserQuery::create()
            ->filterByCompanyId($company ? $company->getId() : null)
            ->filterByPhone($phone)
            ->filterByRegister(false)
            ->filterByUserId(null)
            ->findOne();
    }
}
