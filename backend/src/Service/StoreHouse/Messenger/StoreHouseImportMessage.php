<?php

declare(strict_types=1);

namespace App\Service\StoreHouse\Messenger;

use App\Model\StoreHouseSetting;

class StoreHouseImportMessage
{
    protected StoreHouseSetting $setting;

    public function __construct(StoreHouseSetting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @return StoreHouseSetting
     */
    public function getSetting(): StoreHouseSetting
    {
        return $this->setting;
    }
}
