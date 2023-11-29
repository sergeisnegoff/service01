<?php


namespace App\Admin\Supplier;


use App\Model\UnitQuery;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;
use Propel\Runtime\ActiveQuery\Criteria;

class UnitTable extends TableComponent
{
    /**
     * @title Единицы измерения
     * @header
     * {{ button('Добавить', {size: 'sm', type: 'success', icon: 'plus'}) | open('Supplier.UnitEditor') }}
     *
     * @cols Название, .
     *
     * \Unit
     * @sortable true
     *
     * @col {{ title | open('Supplier.UnitEditor', {key: _key}) | controls }}
     * @col {{ buttons( _visible() ~ _delete() ) }}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param UnitQuery $query
     * @param Scope $scope
     * @param \Creonit\AdminBundle\Component\Scope\ListRowScopeRelation|null $relation
     * @param $relationValue
     * @param $level
     */
    protected function filter(
        ComponentRequest $request,
        ComponentResponse $response,
        $query,
        Scope $scope,
        $relation,
        $relationValue,
        $level
    ) {
        $query
            ->filterByExternalCode('', Criteria::EQUAL)
            ->filterByCompanyId(null, Criteria::ISNULL);
    }
}
