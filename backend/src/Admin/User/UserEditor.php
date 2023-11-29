<?php

namespace App\Admin\User;

use App\Helper\PhoneHelper;
use App\Model\Map\UserTableMap;
use App\Model\User;
use App\Service\User\UserRepository;
use App\Service\User\UserService;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Exception\HandleException;

class UserEditor extends EditorComponent
{
    /**
     * @var PhoneHelper
     */
    protected PhoneHelper $phoneHelper;

    /**
     * @var UserService
     */
    protected UserService $userService;

    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    public function __construct(PhoneHelper $phoneHelper, UserService $userService, UserRepository $userRepository)
    {
        $this->phoneHelper = $phoneHelper;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

    /**
     * @title Пользователь
     * @entity User
     *
     * @event render() {
     *   const $password = this.node.find('input[name="change_password"]');
     *   const $form = this.node.find('form');
     *
     *   const origSubmit = $._data($form.get(0), 'events')['submit'][0].handler;
     *
     *   if(this.query.key) {
     *      $form.off('submit').on('submit', function(e){
     *        e.preventDefault();
     *        const isChangePassword = $password.val() !== '';
     *
     *        var confirmed  = isChangePassword ? confirm('Вы хотите изменить пароль пользователя?') : true;
     *        if (confirmed) {
     *          origSubmit.call(this, e);
     *        }
     *      });
     *    }
     * }
     *
     * @field email {constraints: [Email()]}
     * @field phone {required: true}
     *
     * @template
     * {{ email | text | group('Email') }}
     * {% filter row %}
     *   {{ last_name | text | group('Фамилия') | col(4) }}
     *   {{ first_name | text | group('Имя') | col(4) }}
     *   {{ middle_name | text | group('Отчество') | col(4) }}
     * {% endfilter %}
     * {{ phone | text | group('Телефон*') }}
     * {{ change_password | text | group('Пароль', {notice: 'обязательно при создании'}) }}
     *
     * {% filter group('Группы') %}
     *  {% if _key %}
     *      {{ component('ChooseUserGroupTable', {user_id: _key}) }}
     *  {% else %}
     *      <p>Сохраните запись, чтобы управлять группами пользователя</p>
     *  {% endif %}
     * {% endfilter %}
     *
     * {% if _key %}
     *   {{ component('Supplier.CompanyTable', {userId: _key}) | group('Организации') }}
     * {% else %}
     *   {{ '<p>Сохраните запись, чтобы управлять организациями пользователя</p>' | raw | group('Организации') }}
     * {% endif %}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param User $entity
     *
     * @throws HandleException
     */
    public function preSave(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        $entity->setPhone($this->phoneHelper->normalizePhone($entity->getPhone()));

        if ($entity->isColumnModified(UserTableMap::COL_EMAIL)) {
            if ($entity->getEmail() && $this->userRepository->getUserByEmail($entity->getEmail())) {
                $response->flushError('Такой пользователь уже существует', 'email');
            }
        }

        if ($entity->isColumnModified(UserTableMap::COL_PHONE)) {
            if ($entity->getPhone() && $this->userRepository->getUserByPhone($entity->getPhone())) {
                $response->flushError('Такой пользователь уже существует', 'phone');
            }
        }

        if ($entity->isNew() and !$request->data->get('change_password')) {
            $response->flushError('Введите пароль', 'change_password');
        }
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param User $entity
     */
    public function postSave(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        if ($request->data->get('change_password')) {
            $this->userService->changePassword($entity, $request->data->get('change_password'));
            $entity->save();
        }
    }
}
