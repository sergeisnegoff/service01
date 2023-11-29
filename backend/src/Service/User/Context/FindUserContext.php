<?php

declare(strict_types=1);

namespace App\Service\User\Context;

class FindUserContext
{
    protected ?string $phone;
    protected ?string $email;

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): FindUserContext
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): FindUserContext
    {
        $this->email = $email;
        return $this;
    }
}
