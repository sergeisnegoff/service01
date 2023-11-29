<?php

namespace App\Admin\User;

use App\Admin\ExtraAdminModule;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserModule extends ExtraAdminModule
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure()
    {
        $this
            ->setTitle('Пользователи')
            ->setIcon('user')
            ->setTemplate('UserTable');
    }

    public function initialize()
    {
        $this->addComponentAsService(RoleTable::class);
        $this->addComponentAsService(UserEditor::class);

        $this->addComponentAsService(NotificationTable::class);
        $this->addComponentAsService(NotificationEditor::class);
    }
}
