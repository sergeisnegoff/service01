<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Docrobot;

use App\Model\Company;
use App\Model\DocrobotSetting;
use App\Service\ElectronicDocumentManagement\Docrobot\DocrobotData\DocrobotSettingsData;

class DocrobotSettingsService
{
    public function fillSettings(Company $company, DocrobotSettingsData $data): DocrobotSetting
    {
        $settings = $company->getDocrobotSetting();
        $settings
            ->setLogin($data->getLogin())
            ->setPassword($data->getPassword())
            ->setGln($data->getGln())
            ->save();

        return $settings;
    }
}
