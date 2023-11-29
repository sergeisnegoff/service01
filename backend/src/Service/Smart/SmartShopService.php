<?php

declare(strict_types=1);

namespace App\Service\Smart;

use App\Helper\PropelHelper;
use App\Model\Company;
use App\Model\CompanyOrganizationShop;
use App\Model\CompanyOrganizationShopQuery;
use App\Service\Company\CompanyService;
use App\Service\Company\CompanyUserData\CompanyUserData;
use App\Service\Company\CompanyUserService;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Propel;

class SmartShopService
{
    private CompanyService $companyService;
    private CompanyUserService $companyUserService;

    public function __construct(CompanyService $companyService, CompanyUserService $companyUserService)
    {
        $this->companyService = $companyService;
        $this->companyUserService = $companyUserService;
    }

    public function approve(array $companies, array $shopIds = [])
    {
        $shops = CompanyOrganizationShopQuery::create()->findPks($shopIds);
        $this->approveShops($shops);

        $existCompanies = [];

        foreach ($companies as $company) {
            $findCompany = $this->companyService->retrieveById($company['companyId'] ?? null);

            if (!$findCompany) {
                continue;
            }

            $companyByInn = $this->companyService->retrieveByInn($findCompany->getInn());

            if ($companyByInn->getId() !== $findCompany->getId()) {
                $existCompanies[] = $findCompany->getInn();
                $findCompany->delete();
                continue;
            }

            $user = $findCompany->getUserRelatedByUserId();

            $findCompany
                ->setApproveFromSmart(true)
                ->setType($company['type'] ?? Company::TYPE_BUYER)
                ->save();

            $userData = new CompanyUserData();
            $userData
                ->setPhone($user->getPhone())
                ->setFirstName($user->getFirstName())
            ;

            $this->companyUserService->processCompanyUser($findCompany, $userData, true);
        }

        if ($existCompanies) {
            return ['existCompanies' => $existCompanies];
        }

        return ['success' => true];
    }

    /**
     * @param ObjectCollection|CompanyOrganizationShop[] $shops
     */
    public function approveShops($shops)
    {
        $connection = Propel::getConnection();
        PropelHelper::startTransaction($connection);

        foreach ($shops as $shop) {
            $shop->approveFromSmart();
        }

        PropelHelper::commitTransaction($connection);

        return $shops;
    }
}
