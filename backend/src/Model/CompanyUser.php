<?php

namespace App\Model;

use App\Model\Base\CompanyUser as BaseCompanyUser;

/**
 * Skeleton subclass for representing a row from the 'company_user' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class CompanyUser extends BaseCompanyUser
{
    public function getFullName(): string
    {
        return implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name
        ]));
    }

    public function getHash(): string
    {
        return md5(implode('|', [$this->id, $this->getCreatedAt('Y-m-d H:i:s')]));
    }
}
