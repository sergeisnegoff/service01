<?php


namespace App\Service\User\Exception;


class UserAlreadyExistsException extends \Exception
{
    protected string $username;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return UserAlreadyExistsException
     */
    public function setUsername(string $username): UserAlreadyExistsException
    {
        $this->username = $username;
        return $this;
    }
}
