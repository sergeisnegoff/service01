<?php


namespace App\Admin\Buyer;


use App\Admin\ExtraAdminModule;

class BuyerModule extends ExtraAdminModule
{
    protected function configure()
    {
        $this
            ->setTitle('Покупатели')
            ->setIcon('shopping-basket')
            ->setTemplate('BuyerTable')
        ;
    }

    public function initialize()
    {
        $this->addComponentAsService(BuyerTable::class);
        $this->addComponentAsService(BuyerEditor::class);
    }
}
