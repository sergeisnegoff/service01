<?php
declare(strict_types=1);

namespace App\Admin\ApiRequestLog;

use App\Model\ApiRequestLog;
use App\Model\ApiRequestLogQuery;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;
use Creonit\AdminBundle\Component\Scope\ListRowScopeRelation;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiRequestLogTable extends TableComponent
{
    /**
     * @cols Токен, URL, Метод, Код ответа, Дата запроса
     *
     * \ApiRequestLog
     * @pagination 100
     *
     * @field created_at {load: 'entity.getCreatedAt("d.m.Y H:i")'}
     *
     * @col {{ token | open('ApiRequestLog.ApiRequestLogEditor', {key: _key}) }}
     * @col {{ uri }}
     * @col {{ method }}
     * @col {{ status_code }}
     * @col {{ created_at }}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param ApiRequestLogQuery $query
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
        $query->orderByCreatedAt(Criteria::DESC);
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param ParameterBag $data
     * @param ApiRequestLog $entity
     * @param Scope $scope
     * @param ListRowScopeRelation|null $relation
     * @param $relationValue
     * @param $level
     */
    protected function decorate(
        ComponentRequest $request,
        ComponentResponse $response,
        ParameterBag $data,
        $entity,
        Scope $scope,
        $relation,
        $relationValue,
        $level
    ) {
        $data->set('_row_class', $entity->getRowClass());
    }
}
