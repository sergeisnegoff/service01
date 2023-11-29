<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Diadoc\DiadocData;

use App\Service\DataObject\DataObjectInterface;

class DiadocSettingsData implements DataObjectInterface
{
    protected string $login = '';
    protected string $password = '';
    protected string $apiKey = '';
    protected string $boxId = '';

    /**
     * @return string
     */
    public function getBoxId(): string
    {
        return $this->boxId;
    }

    /**
     * @param string $boxId
     */
    public function setBoxId(string $boxId): self
    {
        $this->boxId = $boxId;
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

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}
