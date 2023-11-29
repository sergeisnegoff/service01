<?php


namespace App\Service\User;


use App\Model\UserGroupQuery;

class UserGroupRepository
{
    public function getGroupByName($name)
    {
        return UserGroupQuery::create()->findOneByName($name);
    }
}
