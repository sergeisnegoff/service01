<?php

namespace App\Admin\Supplier;

use App\Model\Company;
use App\Model\CompanyQuery;
use App\Service\Mercury\MercuryService;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScopeRelation;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;
use Exception;
use Propel\Runtime\ActiveQuery\Criteria;

class SupplierTable extends TableComponent
{
    private MercuryService $mercuryService;

    public function __construct(MercuryService $mercuryService)
    {
        $this->mercuryService = $mercuryService;
    }

    /**
     * @action importVsd(id) {
     *     this.request('importVsd', {key: id}, null, function(response) {
     *         if (this.checkResponse(response)) {
     *             alert('ВСД загружены');
     *         }
     *     });
     * }
     *
     * @title Поставщики
     * @header
     * {{ button('Добавить организацию', {type: 'success', size: 'sm', icon: 'plus'}) | open('SupplierEditor') }}
     * {{ button('Единицы измерения', {size: 'sm', icon: 'bars'}) | open('Supplier.UnitTable') }}
     * {{ button('Статусы приемки', {size: 'sm', icon: 'bars'}) | open('Buyer.InvoiceStatusTable', {type: 1}) }}
     * {{ button('Статусы выгрузки', {size: 'sm', icon: 'bars'}) | open('Buyer.InvoiceStatusTable', {type: 2}) }}
     *
     * <form class="form-inline pull-right">
     *      {{ search | text({placeholder: 'Поиск', size: 'sm'}) }}
     *      {{ submit('Найти', {size: 'sm'}) }}
     *  </form>
     *
     * @cols ., Название, ИНН, КПП, Статус, .
     *
     * \Company
     *
     * @field status {load: 'entity.getVerificationStatusCaption()'}
     *
     * @col {{ id }}
     * @col
     *      {% if _query.external %}
     *          {{ title | action('external', _key, title) }}
     *      {% else %}
     *          {{ title | open('SupplierEditor', {key: _key}) | controls(buttons(button('', {size: 'xs', icon: 'arrow-up'}) | tooltip('Выгрузить список ВСД') | action('importVsd', _key))) }}
     *      {% endif %}
     * @col {{ inn }}
     * @col {{ kpp }}
     * @col {{ status }}
     * @col {{ _delete() }}
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
    protected function filter(
        ComponentRequest $request,
        ComponentResponse $response,
        $query,
        Scope $scope,
        $relation,
        $relationValue,
        $level
    ) {
        $query->filterByType(Company::TYPE_SUPPLIER);

        if ($search = $request->query->get('search')) {
            $query->filterByTitle('%' . $search . '%', Criteria::LIKE)->_or();
            $query->filterByInn('%' . $search . '%', Criteria::LIKE)->_or();
        }
    }

    public function importVsd(ComponentRequest $request, ComponentResponse $response)
    {
        $company = CompanyQuery::create()->findPk($request->query->get('key')) or $response->flushError(
            'Организация не найдена'
        );

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
