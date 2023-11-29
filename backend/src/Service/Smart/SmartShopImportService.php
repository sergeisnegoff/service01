<?php

declare(strict_types=1);

namespace App\Service\Smart;

use App\Helper\PropelHelper;
use App\Model\Company;
use App\Model\User;
use App\Service\Buyer\BuyerOrganizationData\BuyerOrganizationData;
use App\Service\Buyer\BuyerOrganizationData\BuyerOrganizationShopData;
use App\Service\Buyer\BuyerOrganizationService;
use App\Service\Company\CompanyService;
use Exception;
use Propel\Runtime\Propel;

class SmartShopImportService
{
    private SmartClient $client;
    private BuyerOrganizationService $organizationService;
    private CompanyService $companyService;

    public function __construct(
        SmartClient $client,
        CompanyService $companyService,
        BuyerOrganizationService $organizationService
    ) {
        $this->client = $client;
        $this->organizationService = $organizationService;
        $this->companyService = $companyService;
    }

    protected function initImportOptions(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        Propel::disableInstancePooling();
    }

    public function importShops(User $user): void
    {
        $this->initImportOptions();
        $connection = Propel::getConnection();
        $existOrganizations = $this->companyService->getCompaniesForSmartImport($user);

        $importShops = $this->client->getShops($user->getPhone());

        $i = 0;

        foreach ($importShops as $importShop) {
            PropelHelper::startTransaction($connection);

            try {
                $organization = $existOrganizations[$importShop['ИНН']] ?? null;

                if (!$organization) {
                    $organization = $this->companyService->createCompany($user, $importShop['Наименование'], 0, $importShop['ИНН']);
                    $organization->setFromSmart(true)->save();
                    $existOrganizations[$organization->getInn()] = $organization;
                }

                $existShops = $this->organizationService->getShopsForSmartImport($organization);

                $shop = $existShops[$importShop['GUID']] ?? null;

                $shopData = $this->buildShopData($importShop);

                if (!$shop) {
                    $shop = $this->organizationService->createOrganizationShop($organization, $shopData);
                    $importShops[$shop->getExternalCode()] = $shop;

                } else {
                    $this->organizationService->editOrganizationShop($shop, $shopData);
                }

                $i++;

            } catch (Exception $exception) {
                PropelHelper::rollBack($connection);
            }

            if ($i >= 300) {
                PropelHelper::commitTransaction($connection);
            }
        }

        PropelHelper::commitTransaction($connection);
    }

    protected function buildOrganizationData(array $importShop): BuyerOrganizationData
    {
        $data = new BuyerOrganizationData();
        $data
            ->setInn($importShop['ИНН'])
            ->setTitle($importShop['Наименование'])
            ->setFromSmart(true)
        ;

        return $data;
    }

    protected function buildShopData(array $importShop): BuyerOrganizationShopData
    {
        $data = new BuyerOrganizationShopData();
        $data
            ->setTitle((string) $importShop['Наименование'])
            ->setCode((string) $importShop['GUID'])
            ->setLatitude((string) $importShop['Широта'])
            ->setLongitude((string) $importShop['Долгота'])
            ->setAddress((string) $importShop['Адрес'])
            ->setPartnerTitle((string) $importShop['ПартнерНаименование'])
            ->setFromSmart(true)
        ;

        return $data;
    }
}
