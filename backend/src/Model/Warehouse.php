<?php

namespace App\Model;

use App\Model\Base\Warehouse as BaseWarehouse;

/**
 * Skeleton subclass for representing a row from the 'warehouse' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Warehouse extends BaseWarehouse
{
    public function getTitleWithRid(): string
    {
        if (!$this->external_code) {
            return $this->title;
        }

        return sprintf('%s (%s)', $this->title, $this->external_code);
    }
}
