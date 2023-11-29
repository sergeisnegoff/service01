<?php


namespace App\Admin\User;


use Creonit\AdminBundle\Component\TableComponent;

class NotificationTable extends TableComponent
{
    /**
     * @title Уведомления
     * @header
     * {{ button('Добавить', {size: 'sm', icon: 'plus', type: 'success'}) | open('NotificationEditor') }}
     *
     * @cols Название, Код, .
     *
     * \Notification
     *
     * @col {{ system_title | open('NotificationEditor', {key: _key}) }}
     * @col {{ code }}
     * @col {{ _delete() }}
     */
    public function schema()
    {
    }
}
