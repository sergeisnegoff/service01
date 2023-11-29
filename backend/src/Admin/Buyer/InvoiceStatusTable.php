<?php


namespace App\Admin\Buyer;


use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;

class InvoiceStatusTable extends TableComponent
{
    /**
     * @title Статусы
     * @header
     * {{ button('Добавить', {size: 'sm', type: 'success', icon: 'plus'}) | open('Buyer.InvoiceStatusEditor', _query) }}
     *
     * @cols Название, .
     *
     * \InvoiceStatus
     * @sortable true
     *
     * @col {{ title | open('Buyer.InvoiceStatusEditor', {key: _key}) | controls }}
     * @col {{ buttons( _visible() ~ _delete() ) }}
     */
    public function schema()
    {
    }

    protected function filter(ComponentRequest $request, ComponentResponse $response, $query, Scope $scope, $relation, $relationValue, $level)
    {
        $query->filterByType($request->query->get('type'));
    }
}
