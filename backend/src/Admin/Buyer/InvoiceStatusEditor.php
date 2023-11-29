<?php


namespace App\Admin\Buyer;


use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;

class InvoiceStatusEditor extends EditorComponent
{
    /**
     * @title Статус
     * @entity InvoiceStatus
     *
     * @field title {required: true}
     *
     * @template
     * {{ title | text | group('Название') }}
     * {{ code | text | group('Системный код') }}
     */
    public function schema()
    {
    }

    public function preSave(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        if ($entity->isNew()) {
            $entity->setType($request->query->get('type'));
        }
    }
}
