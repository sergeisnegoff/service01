<?php

namespace App\Model;

use App\Model\Base\CompanyOrganizationShopQuery as BaseCompanyOrganizationShopQuery;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Skeleton subclass for performing query and update operations on the 'company_organization_shop' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class CompanyOrganizationShopQuery extends BaseCompanyOrganizationShopQuery
{
    public static function makeSmartQuery(bool $smart = false): self
    {
        $query = self::create();

        if ($smart) {
            $query
                ->filterByFromSmart(true)
                ->filterByApproveFromSmart(false)
            ;

        } else {
            $query->where('(
                (from_smart = true AND approve_from_smart = true) OR
                from_smart = false
            )');
        }

        return $query;
    }

    public static function makeDiadocQuery(): self
    {
        return self::create()->filterByDiadocExternalCode('', Criteria::NOT_EQUAL);
    }

    public static function makeDocrobotQuery(): self
    {
        return self::create()->filterByDocrobotExternalCode('', Criteria::NOT_EQUAL);
    }
}
