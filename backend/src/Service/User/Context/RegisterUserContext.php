<?php


namespace App\Service\User\Context;


class RegisterUserContext
{
    protected string $phone;
    protected string $password;
    protected string $groupCode = '';
    protected string $fullName = '';

    protected bool $createFirstCompany = true;

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateFirstCompany(): bool
    {
        return $this->createFirstCompany;
    }

    /**
     * @param bool $createFirstCompany
     */
    public function setCreateFirstCompany(bool $createFirstCompany): self
    {
        $this->createFirstCompany = $createFirstCompany;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupCode(): string
    {
        return $this->groupCode;
    }

    /**
     * @param string $groupCode
     */
    public function setGroupCode(string $groupCode): self
    {
        $this->groupCode = $groupCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return RegisterUserContext
     */
    public function setPhone(string $phone): RegisterUserContext
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return RegisterUserContext
     */
    public function setPassword(string $password): RegisterUserContext
    {
        $this->password = $password;
        return $this;
    }
}
