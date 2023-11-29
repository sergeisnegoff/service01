<?php
declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\Company;
use App\Model\MercurySetting;
use App\Service\Mercury\MercuryData\MercurySettingsData;

class MercurySettingsService
{
    public function fillSettings(Company $company, MercurySettingsData $data): MercurySetting
    {
        $settings = $company->getMercurySetting();
        $settings
            ->setIssuerId($data->getIssuerId())
            ->setVeterinaryLogin($data->getVeterinaryLogin())
            ->setLogin($data->getLogin())
            ->setPassword($data->getPassword())
            ->setApiKey($data->getApiKey())
            ->save();

        return $settings;
    }

    public function changeAutoRepayment(MercurySetting $setting): MercurySetting
    {
        $setting->setAutoRepayment(!$setting->isAutoRepayment())->save();
        return $setting;
    }
}
