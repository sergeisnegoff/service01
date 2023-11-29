<?php

namespace App\Model;

use App\Model\Base\FormField as BaseFormField;
use Propel\Runtime\Collection\ObjectCollection;

/**
 * Skeleton subclass for representing a row from the 'form_field' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class FormField extends BaseFormField
{
    public const
        CODE_EMAIL = 'email',
        CODE_NAME = 'name';

    public function getFieldName(): string
    {
        return $this->code ?? 'field_' . $this->id;
    }

    public function getTypeCode(): string
    {
        return Form::getTypeNames()[$this->type] ?? '';
    }

    public function getTypeCaption(): string
    {
        return Form::getTypes()[$this->type] ?? '';
    }

    public function isSubject(): bool
    {
        return $this->getType() === Form::FIELD_SUBJECT;
    }

    public function isFile(): bool
    {
        return Form::getTypeName($this->getType()) === 'file';
    }

    public function getOptions(): ObjectCollection
    {
        return FormFieldOptionQuery::create()
            ->orderBySortableRank()
            ->filterByVisible(true)
            ->filterByFormField($this)
            ->find();
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, [Form::FIELD_CHECKBOX, Form::FIELD_RADIO, Form::FIELD_SELECT]);
    }

}
