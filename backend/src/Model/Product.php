<?php

namespace App\Model;

use App\Model\Base\Product as BaseProduct;

/**
 * Skeleton subclass for representing a row from the 'product' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Product extends BaseProduct
{
    public function isEqualOwner(User $user)
    {
        return $this->getCompanyId() === $user->getActiveCompanyId();
    }

    public function getUnitCaption()
    {
        return $this->unit_id ? $this->getUnit()->getTitle() : '';
    }
}
