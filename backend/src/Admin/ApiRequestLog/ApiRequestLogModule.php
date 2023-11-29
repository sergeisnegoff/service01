<?php
declare(strict_types=1);

namespace App\Admin\ApiRequestLog;

use App\Admin\ExtraAdminModule;

class ApiRequestLogModule extends ExtraAdminModule
{
    protected function configure()
    {
        $this
            ->setTitle('Логи API запросов')
            ->setIcon('clipboard')
            ->setTemplate('ApiRequestLogTable')
        ;
    }

    public function initialize()
    {
        $this->addComponentAsService(ApiRequestLogTable::class);
        $this->addComponentAsService(ApiRequestLogEditor::class);
    }
}
