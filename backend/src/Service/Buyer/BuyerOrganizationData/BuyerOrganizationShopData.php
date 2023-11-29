<?php


namespace App\Service\Buyer\BuyerOrganizationData;


use App\Service\DataObject\DataObjectInterface;

class BuyerOrganizationShopData implements DataObjectInterface
{
    protected string $title = '';
    protected string $alternativeTitle = '';
    protected string $code = '';
    protected string $address = '';
    protected string $latitude = '';
    protected string $longitude = '';
    protected string $partnerTitle = '';
    protected bool $fromSmart = false;
    protected bool $approveFromSmart = false;
    protected string $diadocExternalCode = '';
    protected string $docrobotExternalCode = '';

    /**
     * @return string
     */
    public function getAlternativeTitle(): string
    {
        return $this->alternativeTitle;
    }

    /**
     * @param string $alternativeTitle
     */
    public function setAlternativeTitle(string $alternativeTitle): self
    {
        $this->alternativeTitle = $alternativeTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiadocExternalCode(): string
    {
        return $this->diadocExternalCode;
    }

    /**
     * @param string $diadocExternalCode
     */
    public function setDiadocExternalCode(string $diadocExternalCode): self
    {
        $this->diadocExternalCode = $diadocExternalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocrobotExternalCode(): string
    {
        return $this->docrobotExternalCode;
    }

    /**
     * @param string $docrobotExternalCode
     */
    public function setDocrobotExternalCode(string $docrobotExternalCode): self
    {
        $this->docrobotExternalCode = $docrobotExternalCode;
        return $this;
    }

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
    public function getPartnerTitle(): string
    {
        return $this->partnerTitle;
    }

    /**
     * @param string $partnerTitle
     */
    public function setPartnerTitle(string $partnerTitle): self
    {
        $this->partnerTitle = $partnerTitle;
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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     */
    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     */
    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }
}
