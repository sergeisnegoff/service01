<?php

namespace App\Admin\User;


use Creonit\AdminBundle\Component\TableComponent;

class UserGroupTable extends TableComponent
{
    /**
     * @title Группы пользователей
     * @header {{ button('Добавить', {icon: 'plus', size: 'sm', type: 'success'}) | open('UserGroupEditor') }}
     *
     * @cols Название, Код, .
     *
     * \UserGroup
     *
     * @col {{ title | open('UserGroupEditor', {key: _key}) }}
     * @col {{ name }}
     * @col {{ _delete() }}
     */
    public function schema()
    {
    }
}
