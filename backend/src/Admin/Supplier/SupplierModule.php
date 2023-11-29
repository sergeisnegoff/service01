<?php


namespace App\Admin\Supplier;


use App\Admin\ExtraAdminModule;

class SupplierModule extends ExtraAdminModule
{
    protected function configure()
    {
        $this
            ->setTitle('Поставщики')
            ->setIcon('truck')
            ->setTemplate('SupplierTable')
        ;
    }

    public function initialize()
    {
        $this->addComponentAsService(SupplierTable::class);
        $this->addComponentAsService(SupplierEditor::class);

        $this->addComponentAsService(CompanyEditor::class);
        $this->addComponentAsService(CompanyTable::class);

        $this->addComponentAsService(UnitTable::class);
        $this->addComponentAsService(UnitEditor::class);
    }
}
