<?php

declare(strict_types=1);

namespace App\Helper;

use Propel\Runtime\Propel;

class ImportHelper
{
    public static function initImportOptions(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        Propel::disableInstancePooling();
    }
}
