<?php

namespace App\Model;

use App\Model\Base\UserGroup as BaseUserGroup;

/**
 * Skeleton subclass for representing a row from the 'user_group' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class UserGroup extends BaseUserGroup
{
    const
        GROUP_SUPPLIER = 'supplier',
        GROUP_BUYER = 'buyer',
        GROUP_MODERATOR = 'moderator';

    public static function getRegisterGroups()
    {
        return [
            self::GROUP_SUPPLIER,
            self::GROUP_BUYER,
        ];
    }

    public static function get($name): ?UserGroup
    {
        return UserGroupQuery::create()->findOneByName($name);
    }
}
