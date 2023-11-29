<?php


namespace App\Service\Company;


use App\Model\Company;
use App\Model\CompanyFavorite;
use App\Model\CompanyFavoriteQuery;

class CompanyFavoriteService
{
    public function getFavorite(Company $company, Company $favorite): ?CompanyFavorite
    {
        return CompanyFavoriteQuery::create()
            ->filterByCompanyRelatedByCompanyId($company)
            ->filterByCompanyRelatedByFavoriteId($favorite)
            ->findOne();
    }

    public function isFavorite(Company $company, Company $favorite): bool
    {
        return CompanyFavoriteQuery::create()
            ->filterByCompanyRelatedByCompanyId($company)
            ->filterByCompanyRelatedByFavoriteId($favorite)
            ->exists();
    }

    public function createFavorite(Company $company, Company $favorite): CompanyFavorite
    {
        $companyFavorite = new CompanyFavorite();
        $companyFavorite
            ->setCompanyRelatedByCompanyId($company)
            ->setCompanyRelatedByFavoriteId($favorite)
            ->save();

        return $companyFavorite;
    }

    public function deleteFavorite(CompanyFavorite $companyFavorite)
    {
        $companyFavorite->delete();
    }
}
