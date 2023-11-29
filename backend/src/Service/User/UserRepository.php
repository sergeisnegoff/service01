<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Model\User;
use App\Model\UserQuery;
use App\Service\User\Context\FindUserContext;

class UserRepository
{
    public function getUserByEmail($email)
    {
        return UserQuery::create()->findOneByEmail($email);
    }

    public function getUserByPhone($phone)
    {
        return UserQuery::create()->findOneByPhone($phone);
    }

    /**
     * @param int|null $id
     *
     * @return User|array|mixed|null
     */
    public function getUserById(?int $id)
    {
        return UserQuery::create()->findPk($id);
    }

    public function getUserByContext(FindUserContext $context): ?User
    {
        $query = UserQuery::create();

        if ($context->getPhone()) {
            $query->filterByPhone($context->getPhone());

        } elseif ($context->getEmail()) {
            $query->filterByEmail($context->getEmail());

        } else {
            return null;
        }

        return $query->findOne();
    }
}
