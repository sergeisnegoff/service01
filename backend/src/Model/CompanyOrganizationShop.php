<?php

namespace App\Model;

use App\Model\Base\CompanyOrganizationShop as BaseCompanyOrganizationShop;

/**
 * Skeleton subclass for representing a row from the 'company_organization_shop' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class CompanyOrganizationShop extends BaseCompanyOrganizationShop
{
    public function approveFromSmart(): self
    {
        $this->setApproveFromSmart(true)->save();

        return $this;
    }
}
