<?php

declare(strict_types=1);

namespace App\Service\Iiko;

use App\Model\Company;
use App\Model\IikoSetting;

class IikoSettingService
{
    public function fillSettings(Company $company, string $login, string $password, string $url): IikoSetting
    {
        $settings = $company->getIikoSetting();
        $settings
            ->setLogin($login)
            ->setPassword($password)
            ->setUrl($url)
            ->save();

        return $settings;
    }
}
