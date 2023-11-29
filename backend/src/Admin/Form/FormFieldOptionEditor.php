<?php


namespace App\Admin\Form;


use App\Model\Form;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;

class FormFieldOptionEditor extends EditorComponent
{

	/**
	 * @entity FormFieldOption
	 * @title Поле формы
	 *
	 * @field title {constraints: [NotBlank()]}
	 *
	 * @template
	 * {{ title | text | group('Название') }}
	 *
	 */
	public function schema()
	{
	}

	public function preSave(ComponentRequest $request, ComponentResponse $response, $entity)
	{

		if($entity->isNew()){
			$entity->setFieldId($request->query->get('form_field_id'));
		}

	}


}
