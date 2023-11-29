<?php


namespace App\Admin\Form;


use App\Model\FormResult;
use App\Model\FormResultQuery;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;

class FormResultEditor extends EditorComponent
{
	/**
	 * @entity FormResult
	 * @title Результат заполнения формы
	 *
	 * @field created {load: 'entity.getCreatedAt("d.m.Y H:i")'}
	 * @field status:select
	 * @field user_id
	 * @field user_caption {load: 'entity.getUserCaption()'}
	 * @field answered {load: 'entity.isAnswered()'}
	 * @field link_from {load: 'entity.getLinkFrom()'}
	 *
	 * @template
	 * {{ form_title | panel | group('Форма') }}
	 * {{ created | panel | group('Дата и время') }}
	 * {{ status | select | group('Статус') }}
	 *
	 * {{ component('Forms.FormResultFieldTable', {form_result_id: _key}) | group('Ответы') }}
	 *
	 * {% if user_id %}
	 *   {{ user_caption | panel | group('Пользователь') }}
	 * {% endif %}
	 *
	 * {{ link_from | raw | panel | group('URL с которой отправили форму') }}
	 * {{ ip_address | panel | group('IP-адрес') }}
	 *
	 */
	public function schema()
	{
		$this->getField('status')->parameters->set('options', FormResult::getStatuses());
	}

	/**
	 * @param ComponentRequest $request
	 * @param ComponentResponse $response
	 * @param FormResult $entity
     */
	public function validate(ComponentRequest $request, ComponentResponse $response, $entity)
	{
		if($request->data->get('answer') and !$entity->getEmail()){
			$response->error('Невозможно ответить на заявку — не указана электронная почта', 'answer');
		}
	}

	protected function retrieveEntity(ComponentRequest $request, ComponentResponse $response)
    {
        return FormResultQuery::create()->filterById($request->query->get('key'))->findOne();
    }


    /**
	 * @param ComponentRequest $request
	 * @param ComponentResponse $response
	 * @param FormResult $entity
	 */
	public function preSave(ComponentRequest $request, ComponentResponse $response, $entity)
	{
	}
}
