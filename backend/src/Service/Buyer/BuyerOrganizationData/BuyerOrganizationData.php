<?php


namespace App\Service\Buyer\BuyerOrganizationData;


use App\Service\DataObject\DataObjectInterface;

class BuyerOrganizationData implements DataObjectInterface
{
    protected string $title = '';
    protected string $inn = '';
    protected string $kpp = '';
    protected string $code = '';
    protected bool $fromSmart = false;
    protected bool $approveFromSmart = false;

    /**
     * @return bool
     */
    public function isFromSmart(): bool
    {
        return $this->fromSmart;
    }

    /**
     * @param bool $fromSmart
     */
    public function setFromSmart(bool $fromSmart): self
    {
        $this->fromSmart = $fromSmart;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApproveFromSmart(): bool
    {
        return $this->approveFromSmart;
    }

    /**
     * @param bool $approveFromSmart
     */
    public function setApproveFromSmart(bool $approveFromSmart): self
    {
        $this->approveFromSmart = $approveFromSmart;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getInn(): string
    {
        return $this->inn;
    }

    /**
     * @param string $inn
     */
    public function setInn(string $inn): self
    {
        $this->inn = $inn;
        return $this;
    }

    /**
     * @return string
     */
    public function getKpp(): string
    {
        return $this->kpp;
    }

    /**
     * @param string $kpp
     */
    public function setKpp(string $kpp): self
    {
        $this->kpp = $kpp;
        return $this;
    }
}
