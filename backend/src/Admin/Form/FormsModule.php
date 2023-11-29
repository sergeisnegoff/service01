<?php

namespace App\Admin\Form;
use Creonit\AdminBundle\Module;

class FormsModule extends Module
{

	protected function configure()
	{
		$this
			->setTitle('Формы обратной связи')
			->setIcon('list-alt')
			->setTemplate('FormResultTable');
	}

	public function initialize()
	{
		$this->addComponent(new FormResultTable);
		$this->addComponent(new FormTable);
		$this->addComponent(new FormEditor);
		$this->addComponent(new FormFieldTable);
		$this->addComponent(new FormFieldEditor);
		$this->addComponent(new FormResultEditor);
		$this->addComponent(new FormResultFieldTable);
		$this->addComponent(new FormFieldOptionTable);
		$this->addComponent(new FormFieldOptionEditor);
		$this->addComponent(new ChooseFormTable());
		$this->addComponentAsService(FormReportSubjectTable::class);
		$this->addComponentAsService(FormReportSubjectEditor::class);
	}

}
