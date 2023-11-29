<?php


namespace App\Service\User\Context;


class UpdateUserDataContext
{
    protected string $firstName;
    protected string $lastName;
    protected string $phone;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return UpdateUserDataContext
     */
    public function setFirstName(string $firstName): UpdateUserDataContext
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return UpdateUserDataContext
     */
    public function setLastName(string $lastName): UpdateUserDataContext
    {
        $this->lastName = $lastName;
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
     * @return UpdateUserDataContext
     */
    public function setPhone(string $phone): UpdateUserDataContext
    {
        $this->phone = $phone;
        return $this;
    }
}
