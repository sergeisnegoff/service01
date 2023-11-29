<?php


namespace App\Admin\Supplier;


use Creonit\AdminBundle\Component\EditorComponent;

class UnitEditor extends EditorComponent
{
    /**
     * @title Единица измерения
     * @entity Unit
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
