<?php
declare(strict_types=1);

namespace App\Service\Mercury\MercuryData;

use App\Service\DataObject\DataObjectInterface;

class MercurySettingsData implements DataObjectInterface
{
    protected string $issuerId = '';
    protected string $login = '';
    protected string $veterinaryLogin = '';
    protected string $password = '';
    protected string $apiKey = '';

    /**
     * @return string
     */
    public function getVeterinaryLogin(): string
    {
        return $this->veterinaryLogin;
    }

    /**
     * @param string $veterinaryLogin
     */
    public function setVeterinaryLogin(string $veterinaryLogin): self
    {
        $this->veterinaryLogin = $veterinaryLogin;
        return $this;
    }

    /**
     * @return string
     */
    public function getIssuerId(): string
    {
        return $this->issuerId;
    }

    /**
     * @param string $issuerId
     */
    public function setIssuerId(string $issuerId): self
    {
        $this->issuerId = $issuerId;
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
