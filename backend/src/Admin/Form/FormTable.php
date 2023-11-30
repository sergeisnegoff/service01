<?php


namespace App\Admin\Form;


use Creonit\AdminBundle\Component\TableComponent;

class FormTable extends TableComponent
{


	/**
	 * @title Форм
	 * @header
	 * {{ button('Добавить форму', {type: 'success', icon: 'fa-list-alt', size: 'sm'}) | open('Forms.FormEditor') }}
	 *
	 * @cols Название, Идентификатор, .
	 *
	 * \Form
	 * @pagination 100
	 * @sortable true
	 * @col {{ title | open('Forms.FormEditor', {key: _key}) | controls }}
	 * @col {{ code }}
	 * @col {{ buttons(_visible() ~ _delete()) }}
	 *
	 *
	 */
	public function schema()
	{
	}
}
