<?php


namespace App\Admin\Form;


use Creonit\AdminBundle\Component\EditorComponent;

class FormReportSubjectEditor extends EditorComponent
{
    /**
     * @title Тема обращение
     * @entity FormReportSubject
     *
     * @field title {required: true}
     *
     * @template
     * {{ title | text | group('Название') }}
     */
    public function schema()
    {
    }
}
