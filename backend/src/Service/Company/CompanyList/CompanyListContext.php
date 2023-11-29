<?php


namespace App\Service\Company\CompanyList;


use App\Model\User;

class CompanyListContext
{
    protected User $user;
    protected bool $smart = false;

    /**
     * @return bool
     */
    public function isSmart(): bool
    {
        return $this->smart;
    }

    /**
     * @param bool $smart
     */
    public function setSmart(bool $smart): self
    {
        $this->smart = $smart;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
