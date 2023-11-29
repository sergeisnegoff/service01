<?php

namespace App\Model;

use App\Model\Base\InvoiceStatus as BaseInvoiceStatus;

/**
 * Skeleton subclass for representing a row from the 'invoice_status' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class InvoiceStatus extends BaseInvoiceStatus
{
    const
        TYPE_ACCEPTANCE = 1,
        TYPE_DISCHARGE = 2
    ;

    const
        CODE_DISCHARGE = 'discharge',
        CODE_NOT_DISCHARGE = 'notDischarge',
        CODE_NOT_ACCEPTED = 'notAccepted',
        CODE_CANCELED = 'canceled',
        CODE_ACCEPT = 'accept',
        CODE_ACCEPT_PARTIALLY = 'acceptPartially'
    ;

    public static array $typeCodes = [
        self::TYPE_ACCEPTANCE => 'acceptance',
        self::TYPE_DISCHARGE => 'discharge',
    ];

    public static function getTypeId($code): ?int
    {
        $flip = array_flip(self::$typeCodes);
        return $flip[$code] ?? null;
    }

    public static function retrieveByCode($code): ?InvoiceStatus
    {
        return InvoiceStatusQuery::create()->findOneByCode($code);
    }

    public function isAccept(): bool
    {
        return $this->code === self::CODE_ACCEPT;
    }

    public function isDischarge(): bool
    {
        return $this->code === self::CODE_DISCHARGE;
    }
}
