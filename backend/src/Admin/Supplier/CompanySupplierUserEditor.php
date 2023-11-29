<?php


namespace App\Admin\Supplier;

use App\Model\CompanyUser;
use App\Model\CompanyUserQuery;
use App\Model\UserGroup;
use App\Model\UserGroupQuery;
use App\Model\UserGroupRel;
use App\Model\UserGroupRelQuery;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Exception\HandleException;
use Propel\Runtime\Exception\PropelException;

class CompanySupplierUserEditor extends EditorComponent
{
    /**
     * @title Поставщик организации
     * @entity CompanyUser
     *
     * @field active
     * @field registration
     * @field user_id:external {title: 'entity.getUser().getPhone()', required:true}
     * @field company_id:external {title: 'entity.getCompany().getTitle()', required:true}
     *
     * @template
     * {% filter row %}
     *     {{ company_id | external('SupplierTable', { empty: 'Выберите организацию', query: { external:true } }) | group('Организация') | col(12) }}
     *     {{ user_id | external('User.UserTable', { empty: 'Выберите пользователя', query: { external:true } }) | group('Пользователь') | col(12) }}
     *
     * {% if _key %}
     *     {{ email | panel | group('Email') | col(6) }}
     *     {{ phone | panel | group('Телефон') | col(6) }}
     * {% endif %}
     *
     * {% endfilter %}
     *
     * {{ active | checkbox('Активен') }}
     * {{ register | checkbox('Зарегистрирован') }}
     *
     * {{ image_id | image | group('Фото') }}
     */
    public function schema()
    {
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param CompanyUser $entity
     *
     * @throws PropelException|HandleException
     */
    public function preSave(ComponentRequest $request, ComponentResponse $response, $entity)
    {
        parent::preSave($request, $response, $entity);

        $entity->setPhone($entity->getUser()->getPhone());
        $entity->setEmail($entity->getUser()->getEmail());
        $entity->setFirstName($entity->getUser()->getFirstName());

        if ($entity->isNew()) {
            $oldEntity = CompanyUserQuery::create()
                ->filterByCompanyId($entity->getCompanyId())
                ->filterByPhone($entity->getPhone())
                ->findOne();

            if ($oldEntity) {
                $response->flushError('Пользователь компании уже существует');
            }
        }

        $userGroup = UserGroupQuery::create()->filterByName(UserGroup::GROUP_SUPPLIER)->findOne();
        $rel = UserGroupRelQuery::create()->filterByUserId($entity->getUserId())->filterByUserGroup(
            $userGroup
        )->findOne() ?: new UserGroupRel();

        if ($rel->isNew()) {
            $rel
                ->setUserId($entity->getUserId())
                ->setUserGroup($userGroup)
                ->save();
        }
    }

    protected function retrieveEntity(ComponentRequest $request, ComponentResponse $response)
    {
        $entity = parent::retrieveEntity($request, $response);
        if ($request->query->get('company_id')) {
            $entity->setCompanyId($request->query->get('company_id'));
        }
        return $entity;
    }
}
