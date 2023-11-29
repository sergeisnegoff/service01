<?php

namespace App\Model;

use App\Model\Base\CompanyVerificationRequest as BaseCompanyVerificationRequest;

/**
 * Skeleton subclass for representing a row from the 'company_verification_request' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class CompanyVerificationRequest extends BaseCompanyVerificationRequest
{
    const
        STATUS_NEW = 1,
        STATUS_VERIFIED = 2,
        STATUS_BLACK_LIST = 3,
        STATUS_FAILED = 4;

    public static array $statusCaptions = [
        self::STATUS_NEW => 'Не подтвержден',
        self::STATUS_VERIFIED => 'Подтвержден',
        self::STATUS_BLACK_LIST => 'Заблокирован',
        self::STATUS_FAILED => 'Отклонена',
    ];

    public static array $statusCodes = [
        self::STATUS_NEW => 'new',
        self::STATUS_VERIFIED => 'verified',
        self::STATUS_BLACK_LIST => 'blackList',
        self::STATUS_FAILED => 'failed',
    ];

    public static function getCompanyVerificationStatusLabels(): array
    {
        return self::$statusCaptions;
    }

    public function getStatusObject()
    {
        return [
            'id' => $this->status,
            'title' => self::$statusCaptions[$this->status] ?? '',
            'code' => self::$statusCodes[$this->status] ?? '',
        ];
    }

    public static function convertStatusCode($code)
    {
        $flipCodes = array_flip(self::$statusCodes);

        return $flipCodes[$code] ?? null;
    }

    public function isNewStatus()
    {
        return $this->status === self::STATUS_NEW;
    }
}
