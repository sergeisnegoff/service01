<?php

namespace App\Model;

use App\Model\Base\FormResultField as BaseFormResultField;

/**
 * Skeleton subclass for representing a row from the 'form_result_field' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class FormResultField extends BaseFormResultField
{
    public const TINKOFF_NUMBER_FIELD_CODE = 'tinkoff_transaction_number';
    public const TINKOFF_STATUS_FIELD_CODE = 'payment_status';
    public const ACCESS_PRICE_TYPE_FIELD_CODE = 'price_type';

    public const ACCESS_PRICE_TYPE_DISCOUNT_CODE = 'discount';
    public const ACCESS_PRICE_TYPE_NOT_DISCOUNT_CODE = 'not_discount';

    public const
        PAYMENT_STATUS_PAID = 'Оплачено',
        PAYMENT_STATUS_NOT_PAID = 'Не оплачено';

    public const PAYMENT_STATUS_CAPTIONS = [
        self::PAYMENT_STATUS_PAID => self::PAYMENT_STATUS_PAID,
        self::PAYMENT_STATUS_NOT_PAID => self::PAYMENT_STATUS_NOT_PAID,
    ];

    public function getFieldTitle()
    {
        return $this->getFormField()->getTitle();
    }

    public function getAnswerValue()
    {
        if ($this->getFormField()->isFile()) {
            return '<a target="_blank" href="' . $this->getFilePath() . '">Скачать файл</a>';

        } else if ($this->getSubjectId()) {
            return $this->getFormReportSubject()->getTitle();

        } else {
            return $this->value;
        }
    }

}
