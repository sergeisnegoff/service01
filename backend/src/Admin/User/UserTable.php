<?php

namespace App\Admin\User;


use App\Model\Map\CompanyUserTableMap;
use App\Model\Map\UserTableMap;
use App\Model\User;
use App\Model\UserQuery;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScopeRelation;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserTable extends TableComponent
{
    /**
     * @title Пользователи
     *
     * @header
     * {{ button('Добавить', {type: 'success', size: 'sm', icon: 'plus'}) | open('UserEditor') }}
     * {{ button('Группы пользователей', {icon: 'users', size: 'sm'}) | open('UserGroupTable') }}
     * {{ button('Уведомления', {icon: 'bell', size: 'sm'}) | open('NotificationTable') }}
     *
     * <form class="form-inline pull-right">
     *      {{ search | text({placeholder: 'Поиск', size: 'sm'}) }}
     *      {{ submit('Найти', {size: 'sm'}) }}
     *  </form>
     *
     * @cols ., Имя пользователя, Группы, .
     *
     * \User
     * @field phone
     * @field email
     *
     * @col {{ id }}
     * @col
     *      {% if _query.external %}
     *          {{ (email ? email : phone ) | icon('user') | action('external', _key, phone) }}
     *      {% else %}
     *          {{ (email ? email : phone ) | icon('user') | open('UserEditor', {key: _key}) }}
     *      {% endif %}
     * @col {{ groups }}
     * @col {{ _delete() }}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param ParameterBag $data
     * @param User $entity
     * @param Scope $scope
     * @param ListRowScopeRelation|null $relation
     * @param $relationValue
     * @param $level
     */
    protected function decorate(ComponentRequest $request, ComponentResponse $response, ParameterBag $data, $entity, Scope $scope, $relation, $relationValue, $level)
    {
        $data->set('name', implode(' ', array_filter([
            $entity->getLastName(),
            $entity->getFirstName(),
            $entity->getMiddleName()
        ])));

        $data->set('groups', implode(', ', $entity->getUserGroups()->getColumnValues('Title')));
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param UserQuery $query
     * @param Scope $scope
     * @param ListRowScopeRelation|null $relation
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
        if ($search = $request->query->get('search')) {
            $query
                ->useCompanyUserQuery()
                ->endUse()
                ->distinct()
                ->condition('c1', sprintf('%s LIKE ?', UserTableMap::COL_EMAIL), "%{$search}%")
                ->condition('c2', sprintf('%s LIKE ?', UserTableMap::COL_PHONE), "%{$search}%")
                ->condition('c3', sprintf('%s LIKE ?', CompanyUserTableMap::COL_FIRST_NAME), "%{$search}%")
                ->where(['c1', 'c2', 'c3'], Criteria::LOGICAL_OR);
        }
    }
}
