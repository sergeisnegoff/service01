<?php

namespace App\Model;

use App\Model\Base\CompanyUserRule as BaseCompanyUserRule;

/**
 * Skeleton subclass for representing a row from the 'company_user_rule' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class CompanyUserRule extends BaseCompanyUserRule
{
    public const
        RULE_INVOICE = 'ruleInvoice',
        RULE_MERCURI = 'ruleMercuri',
        RULE_CREATE_USER = 'ruleCreateUser',
        RULE_PRODUCT = 'ruleProduct'
    ;

    public static function getSupplierRules(): array
    {
        return [
            self::RULE_INVOICE,
            self::RULE_CREATE_USER,
            self::RULE_PRODUCT,
        ];
    }

    public static function getBuyerRules(): array
    {
        return [
            self::RULE_INVOICE,
            self::RULE_CREATE_USER,
            self::RULE_MERCURI,
        ];
    }
}
