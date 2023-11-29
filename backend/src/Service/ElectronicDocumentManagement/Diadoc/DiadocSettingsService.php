<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Diadoc;

use App\Model\Company;
use App\Model\DiadocSetting;
use App\Service\ElectronicDocumentManagement\Diadoc\DiadocData\DiadocSettingsData;

class DiadocSettingsService
{
    public function fillSettings(Company $company, DiadocSettingsData $data): DiadocSetting
    {
        $settings = $company->getDiadocSetting();
        $settings
            ->setLogin($data->getLogin())
            ->setPassword($data->getPassword())
            ->setApiKey($data->getApiKey())
            ->setBoxId($data->getBoxId())
            ->save();

        return $settings;
    }
}
