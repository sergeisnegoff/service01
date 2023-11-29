<?php

namespace App\Admin\Supplier;

use App\Model\Company;
use App\Model\CompanyVerificationRequest;
use App\Service\Company\CompanyVerificationRequestService;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;

class CompanyEditor extends EditorComponent
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
     * @field verification_status:select
     * @field answer
     * @field site
     * @field email {constraints: [Email()]}
     * @field user_id {title: 'entity.getUserRelatedByUserId().getPhone()'}
     *
     * @template
     * {% filter row %}
     *     {{ user_id | panel | group('Пользователь') | col(12) }}
     *     {{ title | text | group('Название') | col(6) }}
     *     {{ email | text | group('E-mail') | col(6) }}
     *     {{ site | text | group('Сайт') | col(6) }}
     *     {{ inn | text | group('ИНН') | col(6) }}
     *     {{ kpp | text | group('КПП') | col(6) }}
     *     {{ diadoc_external_code | text | group('ID ящика Диадок') | col(6) }}
     *     {{ docrobot_external_code | text | group('GLN в Е-Ком') | col(6) }}
     *     {{ storehouse_external_code | text | group('ID в storehouse') | col(6) }}
     *     {{ delivery_term | text | group('Условия доставки') | col(6) }}
     *     {{ payment_term | text | group('Условия оплаты') | col(6) }}
     *     {{ min_order_amount | text | group('Минимальная сумма заказа') | col(6) }}
     *     {{ description | text | group('Описание') | col(6) }}
     * {% endfilter %}
     *
     * {% filter panel('info', 'Заявка на модерацию') %}
     *     {% filter row %}
     *         {{ verification_status | select | group('Статус') | col(answer ? 6 : 12) }}
     *         {% if answer %}
     *             {{ answer | text | group('Ответ') | col(6) }}
     *         {% endif %}
     *     {% endfilter %}
     * {% endfilter %}
     *
     * {% filter group('Пользователи') %}
     *     {{ component('CompanyUserTable', {companyId: _key}) }}
     * {% endfilter %}
     *
     * {{ image_id | image | group('Логотип') }}
     * {{ gallery_id | gallery | group('Галерея') }}
     */
    public function schema()
    {
        $this->getField('verification_status')->setOptions(
            ['' => 'Выберите статус'] +
            CompanyVerificationRequest::getCompanyVerificationStatusLabels()
        );
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param Company $entity
     */
    public function decorate(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        $companyVerificationRequest = $this->verificationRequestService->getLastVerificationRequest($entity);
        $response->data->set('answer', $companyVerificationRequest ? $companyVerificationRequest->getAnswer() : '');
    }
}
