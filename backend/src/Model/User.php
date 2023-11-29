<?php

namespace App\Model;

use App\Helper\MemoizationTrait;
use App\Model\Base\User as BaseUser;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class User extends BaseUser implements UserInterface, EquatableInterface
{
    use MemoizationTrait;

    protected ?array $roles = null;

    protected static array $rolesCache = [];

    protected string $username = '';

    public function getRoles()
    {
        if (null !== $this->roles) {
            return $this->roles;
        }

        return $this->roles = $this->buildRoles();
    }

    protected function buildRoles()
    {
        if (array_key_exists($this->getId(), static::$rolesCache)) {
            return static::$rolesCache[$this->getId()];
        }

        $roles = [];

        foreach ($this->getUserGroupRelsJoinUserGroup() as $groupRel) {
            $roles = array_merge($roles, $groupRel->getUserGroup()->getRoles());
        }

        return static::$rolesCache[$this->getId()] = array_filter(array_unique($roles));
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        $buildRoles = $this->buildRoles();

        return !array_diff($this->roles, $buildRoles) and !array_diff($buildRoles, $this->roles);
    }

    public function getUserGroups()
    {
        return $this->memoization(
            sprintf('user_%d_groups', $this->id),
            fn() => new ObjectCollection($this->getUserGroupRels()->getColumnValues('UserGroup'))
        );
    }

    public function hasGroup($name)
    {
        $groups = $this->getUserGroups();
        return $groups->contains(UserGroup::get($name));
    }

    public function isSupplier()
    {
        return $this->hasGroup(UserGroup::GROUP_SUPPLIER);
    }

    public function isBuyer()
    {
        return $this->hasGroup(UserGroup::GROUP_BUYER);
    }

    public function isModerator()
    {
        return $this->hasGroup(UserGroup::GROUP_MODERATOR);
    }

    public function isSubordinate()
    {
        return CompanyUserQuery::create()->filterByUser($this)->exists();
    }

    public function getCompanyUser(?Company $company = null)
    {
        if (!$company) {
            return null;
        }

        $companyUser = CompanyUserQuery::create()->filterByCompany($company)->filterByUser($this)->findOne();

        if (!$companyUser) {
            return null;
        }

        $company = $companyUser->getCompany();

        if ($company->getUserId() === $this->id) {
            return null;
        }

        return $companyUser;
    }

    public function completeFirstImportSmart()
    {
        $this->setFirstImportSmartCompleted(true)->save();
    }
}
