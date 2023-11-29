<?php

namespace App\Model;

use App\Model\Base\ProductManufacturer as BaseProductManufacturer;

/**
 * Skeleton subclass for representing a row from the 'product_manufacturer' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class ProductManufacturer extends BaseProductManufacturer
{
    public function isEqualOwner(User $user)
    {
        return $this->getCompanyId() === $user->getActiveCompanyId();
    }
}
