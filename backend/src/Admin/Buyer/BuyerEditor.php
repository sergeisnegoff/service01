<?php

namespace App\Admin\Buyer;

use App\Model\Company;
use App\Service\Company\CompanyVerificationRequestService;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;

class BuyerEditor extends EditorComponent
{
    /**
     * @var CompanyVerificationRequestService
     */
    private CompanyVerificationRequestService $verificationRequestService;

    public function __construct(CompanyVerificationRequestService $verificationRequestService)
    {
        $this->verificationRequestService = $verificationRequestService;
    }

    /**
     * @title Организация
     * @entity Company
     *
     * @field answer
     * @field site
     * @field user_id:external {title: 'entity.getUserRelatedByUserId().getPhone()', required:true}
     *
     * @template
     * {% filter row %}
     *     {{ user_id | external('User.UserTable', { empty: 'Выберите пользователя', query: { external:true } }) | group('Пользователь') | col(12) }}
     *     {{ title | text | group('Название') | col(6) }}
     *     {{ email | text | group('E-mail') | col(6) }}
     *     {{ site | text | group('Сайт') | col(6) }}
     *     {{ inn | text | group('ИНН') | col(6) }}
     *     {{ kpp | panel | group('КПП') | col(6) }}
     *     {{ diadoc_external_code | panel | group('ID ящика Диадок') | col(6) }}
     *     {{ docrobot_external_code | panel | group('GLN в Е-Ком') | col(6) }}
     *     {{ storehouse_external_code | panel | group('ID в storehouse') | col(6) }}
     *     {{ delivery_term | panel | group('Условия доставки') | col(6) }}
     *     {{ payment_term | panel | group('Условия оплаты') | col(6) }}
     *     {{ min_order_amount | panel | group('Минимальная сумма заказа') | col(6) }}
     *     {{ description | text | group('Описание') | col(6) }}
     * {% endfilter %}
     *
     * {% filter panel('info', 'Заявка на модерацию') %}
     *     {% filter row %}
     *         {{ verification_status | panel | group('Статус') | col(answer ? 6 : 12) }}
     *         {% if answer %}
     *             {{ answer | panel | group('Ответ') | col(6) }}
     *         {% endif %}
     *     {% endfilter %}
     * {% endfilter %}
     *
     * {% filter group('Пользователи') %}
     *     {{ component('CompanyBuyerUserTable', {companyId: _key}) }}
     * {% endfilter %}
     *
     * {{ image_id | image | group('Логотип') }}
     * {{ gallery_id | gallery | group('Галерея') }}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param Company $entity
     */
    public function decorate(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        $companyVerificationRequest = $this->verificationRequestService->getLastVerificationRequest($entity);
        $response->data->set('verification_status', $entity->getVerificationStatusCaption());
        $response->data->set('answer', $companyVerificationRequest ? $companyVerificationRequest->getAnswer() : '');
    }

    public function preSave(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        parent::preSave($request, $response, $entity);

        $entity->setType(Company::TYPE_BUYER);
    }
}
