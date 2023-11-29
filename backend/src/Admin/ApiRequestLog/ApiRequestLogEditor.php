<?php
declare(strict_types=1);

namespace App\Admin\ApiRequestLog;

use Creonit\AdminBundle\Component\EditorComponent;

class ApiRequestLogEditor extends EditorComponent
{
    /**
     * @title Информация о запросе
     * @entity ApiRequestLog
     *
     * @field created_at {load: 'entity.getCreatedAt("d.m.Y H:i")'}
     * @field request_data {load: 'entity.getRequestData(true)'}
     * @field response_data {load: 'entity.getResponseData(true)'}
     * @field company_id
     *
     * @template
     * {% if company_id %}
     *     {{ 'Организация' | open('Supplier.CompanyEditor', {key: company_id}) }}
     *     <br>
     *     <br>
     * {% endif %}
     * {{ created_at | panel('info') | group('Дата запроса') }}
     * {% filter row %}
     *     {{ uri | panel | group('URL запроса') | col(4) }}
     *     {{ method | panel | group('Метод') | col(4) }}
     *     {{ status_code | panel | group('Код ответа') | col(4) }}
     *     {{ request_data | raw | panel | group('Запрос') | col(6) }}
     *     {{ response_data | raw | panel | group('Ответ') | col(6) }}
     * {% endfilter %}
     */
    public function schema()
    {
    }
}
