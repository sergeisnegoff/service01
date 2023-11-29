<?php

declare(strict_types=1);

namespace App\Service\StoreHouse;

use App\Model\Company;
use App\Model\StoreHouseSetting;

class StoreHouseSettingService
{
    public function fillSettings(
        Company $company,
        string $login,
        string $password,
        string $ip,
        string $port,
        string $rid,
        ?int $warehouseId = null
    ): StoreHouseSetting {
        $setting = $company->getStoreHouseSetting();

        $setting
            ->setWarehouseId($warehouseId)
            ->setLogin($login)
            ->setPassword($password)
            ->setIp($ip)
            ->setPort($port)
            ->setRid($rid)
            ->save();

        return $setting;
    }
}
