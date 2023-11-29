<?php

namespace App\Admin\Supplier;

use App\Model\CompanyUser;
use App\Model\CompanyUserQuery;
use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Exception\HandleException;
use Propel\Runtime\Exception\PropelException;

class CompanyUserEditor extends EditorComponent
{
    /**
     * @title Пользователь организации
     * @entity CompanyUser
     *
     * @field active
     * @field registration
     * @field user_id:external {title: 'entity.getUser().getPhone()', required:true}
     * @field company_id:external {title: 'entity.getCompany().getTitle()', required:true}
     *
     * @template
     * {% filter row %}
     *     {{ company_id | external('CompanyTable', { empty: 'Выберите организацию', query: { external:true } }) | group('Организация') | col(12) }}
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
     * @throws HandleException|PropelException
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
    }
}
