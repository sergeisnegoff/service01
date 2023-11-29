<?php

namespace App\Model;

use App\Model\Base\CompanyQuery as BaseCompanyQuery;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Skeleton subclass for performing query and update operations on the 'company' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class CompanyQuery extends BaseCompanyQuery
{
    public function findOneByExternalCode(string $code): ?Company
    {
        return $this
            ->filterByDiadocExternalCode($code)->_or()
            ->filterByDocrobotExternalCode($code)->_or()
            ->filterByStorehouseExternalCode($code)->_or()
            ->useCompanyCommentRelatedByCommentIdQuery(null, Criteria::LEFT_JOIN)
                ->filterByExternalCode($code)
            ->endUse()
            ->findOne();
    }
}
