<?php


namespace App\Admin\Form;


use Creonit\AdminBundle\Component\TableComponent;

class FormReportSubjectTable extends TableComponent
{
    /**
     * @title Темы обращения
     * @header
     * {{ button('Добавить', {size: 'sm', type: 'success', icon: 'plus'}) | open('FormReportSubjectEditor') }}
     *
     * @cols Название, .
     *
     * \FormReportSubject
     * @sortable true
     *
     * @col {{ title | open('FormReportSubjectEditor', {key: _key}) | controls }}
     * @col {{ buttons( _visible() ~ _delete() ) }}
     */
    public function schema()
    {
    }
}
