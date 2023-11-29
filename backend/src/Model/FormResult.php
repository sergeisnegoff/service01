<?php

namespace App\Model;

use App\Model\Base\FormResult as BaseFormResult;

/**
 * Skeleton subclass for representing a row from the 'form_result' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class FormResult extends BaseFormResult
{
    const STATUS_NEW = 1;
    const STATUS_PROGRESS = 2;
    const STATUS_PROCESSED = 3;

    protected $content;

    public static $statuses = [
        self::STATUS_NEW => 'Новая',
        self::STATUS_PROGRESS => 'В обработке',
        self::STATUS_PROCESSED => 'Обработана'
    ];

    public static function getStatuses()
    {
        return self::$statuses;
    }

    public function getCaption()
    {
        return $this->form_title;
    }

    public function getStatusCaption()
    {
        return isset(self::getStatuses()[$this->status]) ? self::getStatuses()[$this->status] : '';
    }

    public function isAnswered()
    {
        return $this->getAnsweredAt() ? true : false;
    }

    public function getUserCaption()
    {
        return $this->user_id ? ('ID: ' . $this->getUser()->getId() . ' (' . $this->getUser()->getPhone() . ')') : '';
    }

    public function getLinkFrom()
    {
        return '<a target="_blank" href="' . $this->url_from . '">' . $this->url_from . '</a>';
    }

    public function getShortContent()
    {
        $content = '<table cellpadding="5" border="1">';

        foreach ($this->getFormResultFieldsJoinFormField() as $field) {
            if ($field->getFormField()->isFile()) {
                continue;
            }
            $content .= "<tr><td>{$field->getFieldTitle()}</td><td>{$field->getAnswerValue()}</td>";
        }

        $content .= '</table>';
        return $content;
    }

    public function getStringContent()
    {
        $content = $this->getForm()->getTitle() . ': ';
        $first = true;

        foreach ($this->getFormResultFieldsJoinFormField() as $field) {
            if ($field->getFormField()->isFile()) {
                continue;
            }

            $content .= ($first ? '' : ', ') . $field->getFieldTitle() . ': ' . $field->getAnswerValue();
            $first = false;
        }

        return $content;
    }

    public function getContent()
    {
        if (!$this->content) {
            $content = '<table cellpadding="5" border="1">';

            foreach ($this->getFormResultFieldsJoinFormField() as $field) {
                $content .= "<tr><td>{$field->getFieldTitle()}</td><td>{$field->getAnswerValue()}</td>";
            }

            $content .= '</table>';
            $this->content = $content;
        }

        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getFieldValue($field)
    {
        $field = FormResultFieldQuery::create()
            ->useFormFieldQuery()
            ->filterByCode($field)
            ->endUse()
            ->filterByFormResult($this)
            ->findOne();

        if ($field) {
            return $field->getValue();
        } else {
            return '';
        }

    }

    public function getEmail()
    {
        return $this->getFieldValue('email');
    }

    public function getNameFull()
    {
        if ($this->getFieldValue('name')) {
            $name = trim($this->getFieldValue('name'));
        } else {
            $name = trim(
                $this->getFieldValue('lastname') . ' ' .
                $this->getFieldValue('firstname') . ' ' .
                $this->getFieldValue('middlename')
            );
        }

        return $name;
    }

    public function getName()
    {
        return $this->getFieldValue('name');
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->getFieldValue('phone');
    }

    public function getFieldAnswer($field)
    {
        if (is_string($field)) {
            $field = FormFieldQuery::create()
                ->filterByCode($field)
                ->filterByForm($this->getForm())
                ->select('id')
                ->findOne();
        }

        $resultField = FormResultFieldQuery::create()
            ->filterByFormResult($this)
            ->filterByFieldId($field)
            ->findOne();

        return $resultField ? $resultField->getAnswerValue() : '';
    }

    public function getFormCode()
    {
        return $this->getForm()->getCode();
    }
}
