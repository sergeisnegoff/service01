<?php


namespace App\Admin\Form;


use App\Model\FormFieldQuery;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;

class FormFieldTable extends TableComponent
{

	/**
	 * @title Поля формы
	 * @header
	 * {{ button('Добавить поле', {type: 'success', icon: 'list', size: 'sm'}) | open('Forms.FormFieldEditor', {form_id: _query.form_id}) }}
	 *
	 * @cols Название, Тип, ID, .
	 *
	 * \FormField
	 * @sortable true
	 * @field type {load: 'entity.getTypeCaption()'}
	 *
	 * @col {{ title | open('Forms.FormFieldEditor', {key: _key}) | controls }}
	 * @col {{ type }}
	 * @col {{ id }}
	 * @col {{ buttons(_visible() ~ _delete()) }}
	 *
	 *
	 */
	public function schema()
	{
	}

	/**
	 * @param ComponentRequest $request
	 * @param ComponentResponse $response
	 * @param FormFieldQuery $query
	 * @param Scope $scope
	 * @param $relation
	 * @param $relationValue
	 * @param $level
	 */
	protected function filter(ComponentRequest $request, ComponentResponse $response, $query, Scope $scope, $relation, $relationValue, $level)
	{
		$query->filterByFormId($request->query->get('form_id'));
	}
}
