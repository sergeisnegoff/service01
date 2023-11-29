<?php

namespace App\Model;

use App\Model\Base\Form as BaseForm;
use Propel\Runtime\Collection\ObjectCollection;

/**
 * Skeleton subclass for representing a row from the 'form' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Form extends BaseForm
{
    const FIELD_TEXT = 1;
    const FIELD_TEXTAREA = 2;
    const FIELD_SELECT = 3;
    const FIELD_FILE = 4;
    const FIELD_CHECKBOX = 5;
    const FIELD_RADIO = 6;
    const FIELD_SUBJECT = 7;

    const VALIDATION_ALPHABETICAL = 1;
    const VALIDATION_DIGITS = 2;
    const VALIDATION_EMAIL = 3;
    const VALIDATION_PHONE = 4;
    const VALIDATION_FILE = 5;

    public static $validation_types = [
        self::VALIDATION_ALPHABETICAL => 'Буквы и пробелы',
        self::VALIDATION_DIGITS => 'Цифры',
        self::VALIDATION_EMAIL => 'Email',
        self::VALIDATION_PHONE => 'Телефон',
        self::VALIDATION_FILE => 'Файл',
    ];

    public static function getValidationTypes()
    {
        return self::$validation_types;
    }

    public static function getValidationTypeCodes()
    {
        return self::$field_validate_types ;
    }

    public static $field_types = [
        self::FIELD_TEXT => 'Однострочный текст',
        self::FIELD_TEXTAREA => 'Многострочный текст',
        self::FIELD_SELECT => 'Выбор из списка',
        self::FIELD_FILE => 'Файл',
        self::FIELD_CHECKBOX => 'Множественный выбор',
        self::FIELD_SUBJECT => 'Тема обращения',
        //self::FIELD_RADIO => 'Радио',
    ];

    public static function getTypes()
    {
        return self::$field_types;
    }

    public static $field_type_names = [
        self::FIELD_TEXT => 'text',
        self::FIELD_TEXTAREA => 'textarea',
        self::FIELD_SELECT => 'select',
        self::FIELD_FILE => 'file',
        self::FIELD_CHECKBOX => 'checkbox',
        self::FIELD_RADIO => 'radio'
    ];

    public static function getTypeNames()
    {
        return self::$field_type_names;
    }

    public static function getTypeName($type)
    {
        return self::getTypeNames()[$type] ?? '';
    }

    public static $field_validate_types = [
        1 => 'alphabetical',
        2 => 'digits',
        3 => 'email',
        4 => 'ru_phone',
        5 => 'postal_code'
    ];

    public static function getValidateType($type)
    {
        return self::getValidationTypes()[$type] ?? '';
    }

    public static function getValidateTypeCode($type)
    {
        return self::getValidationTypeCodes()[$type] ?? '';
    }

    /**
     * @return FormField[]|ObjectCollection
     */
    public function getFields()
    {
        return FormFieldQuery::create()
            ->filterByVisible(true)
            ->filterByFormId($this->id)
            ->orderBySortableRank()
            ->find();
    }
}
