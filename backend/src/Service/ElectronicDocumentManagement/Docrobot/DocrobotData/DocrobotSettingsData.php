<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Docrobot\DocrobotData;

use App\Service\DataObject\DataObjectInterface;

class DocrobotSettingsData implements DataObjectInterface
{
    protected string $login = '';
    protected string $password = '';
    protected string $gln = '';

    /**
     * @return string
     */
    public function getGln(): string
    {
        return $this->gln;
    }

    /**
     * @param string $gln
     */
    public function setGln(string $gln): self
    {
        $this->gln = $gln;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;
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
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
}
