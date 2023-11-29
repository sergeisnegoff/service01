<?php


namespace App\Admin\Supplier;


use App\Model\CompanyQuery;
use App\Service\Mercury\MercuryService;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScopeRelation;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;
use Exception;

class CompanyTable extends TableComponent
{
    private MercuryService $mercuryService;

    public function __construct(MercuryService $mercuryService)
    {
        $this->mercuryService = $mercuryService;
    }

    /**
     * @title Организации
     *
     * @action importVsd(id) {
     *     this.request('importVsd', {key: id}, null, function(response) {
     *         if (this.checkResponse(response)) {
     *             alert('ВСД загружены');
     *         }
     *     });
     * }
     *
     * @cols ., Название, ИНН, КПП, Статус
     *
     * \Company
     * @field status {load: 'entity.getVerificationStatusCaption()'}
     *
     * @col {{ id }}
     * @col {{ title | open('Supplier.CompanyEditor', {key: _key}) | controls(buttons(
     *     button('', {size: 'xs', icon: 'arrow-up'}) | tooltip('Выгрузить список ВСД') | action('importVsd', _key)
     * )) }}
     * @col {{ inn }}
     * @col {{ kpp }}
     * @col {{ status }}
     */
    public function schema()
    {
        $this->setHandler('importVsd', [$this, 'importVsd']);
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param CompanyQuery $query
     * @param Scope $scope
     * @param ListRowScopeRelation|null $relation
     * @param $relationValue
     * @param $level
     */
    protected function filter(ComponentRequest $request, ComponentResponse $response, $query, Scope $scope, $relation, $relationValue, $level)
    {
        if ($userId = $request->query->get('userId')) {
            $query
                ->filterByUserId($userId)
                ->_or()
                ->useCompanyUserQuery()
                    ->filterByUserId($userId)
                ->endUse()
                ->distinct();
        }
    }

    public function importVsd(ComponentRequest $request, ComponentResponse $response)
    {
        $company = CompanyQuery::create()->findPk($request->query->get('key')) or $response->flushError('Организация не найдена');

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $this->mercuryService->importVeterinaryDocuments($company->getMercurySetting());

        } catch (Exception $exception) {
            $response->flushError('Произошла ошибка');
        }

        $response->sendSuccess();
    }
}
