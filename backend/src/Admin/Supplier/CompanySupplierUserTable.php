<?php

namespace App\Admin\Supplier;

use App\Model\CompanyUserQuery;
use App\Model\UserGroup;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScopeRelation;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;

class CompanySupplierUserTable extends TableComponent
{
    /**
     * @title Поставщики организации
     *
     * @header
     *
     * {{ button('Добавить пользователя', {type: 'success', size: 'sm', icon: 'plus'}) | open('CompanySupplierUserEditor', {company_id: _query.companyId}) }}
     *
     * @cols Пользователь, Активен
     *
     * \CompanyUser
     * @field phone {load: 'entity.getUser().getPhone()'}
     * @field active
     *
     * @col {{ phone | open('CompanySupplierUserEditor', {key: _key}) }}
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
        $query
            ->filterByCompanyId($request->query->get('companyId'))
                ->useUserQuery()
                    ->useUserGroupRelQuery()
                        ->useUserGroupQuery()
                            ->filterByName(UserGroup::GROUP_SUPPLIER)
                        ->endUse()
                    ->endUse()
                ->endUse();
    }
}
