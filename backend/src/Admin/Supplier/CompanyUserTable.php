<?php

namespace App\Admin\Supplier;

use App\Model\CompanyUserQuery;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScopeRelation;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;

class CompanyUserTable extends TableComponent
{
    /**
     * @title Пользователи организации
     *
     * @header
     *
     * @cols Пользователь, Активен
     *
     * \CompanyUser
     * @field phone
     * @field active
     *
     * @col {{ phone }}
     * @col {{ (active ? 'Да' : 'Нет') }}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param CompanyUserQuery $query
     * @param Scope $scope
     * @param ListRowScopeRelation|null $relation
     * @param $relationValue
     * @param $level
     */
    protected function filter(ComponentRequest $request, ComponentResponse $response, $query, Scope $scope, $relation, $relationValue, $level)
    {
        $query->filterByCompanyId($request->query->get('companyId'));
    }
}
