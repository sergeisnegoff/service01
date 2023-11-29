<?php

namespace App\Admin\User;

use App\Model\UserGroup;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;

class UserGroupEditor extends EditorComponent
{
    /**
     * @title Группа пользователей
     *
     * @action chooseRole(key, state) {
     *  const $input = _this.node.find('input[name="roles"]');
     *  const roles = $input.val().split(',');
     *  if (state) {
     *      roles.push(key);
     *
     *  } else {
     *      var index = roles.indexOf(key);
     *      if (index >= 0) {
     *          roles.splice(roles.indexOf(key), 1);
     *      }
     *  }
     *
     *  $input.val(roles.join(','))
     * }
     *
     * @entity UserGroup
     *
     * @field title {required: true}
     * @field name {required: true}
     *
     * @template
     * {{ title | text | group('Название') }}
     * {{ name | text | group('Код') }}
     *
     * {{ roles | input('hidden') }}
     * {{ component('RoleTable', {actives: roles}) | group('Разрешения') }}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param UserGroup $entity
     */
    public function decorate(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        $response->data->set('roles', $entity->getRoles());
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param UserGroup $entity
     */
    public function preSave(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        $roles = explode(',', $request->data->get('roles', ''));
        $entity->setRoles($roles);
    }
}
