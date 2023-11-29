<?php


namespace App\Service\ProductExport;


use App\Model\Company;
use App\Service\DataObject\DataObjectInterface;

class ProductExportData implements DataObjectInterface
{
    protected array $productsId = [];
    protected array $fields = [];
    protected bool $all = false;
    protected ?Company $company = null;

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company|null $company
     */
    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAll(): bool
    {
        return $this->all;
    }

    /**
     * @param bool $all
     */
    public function setAll(bool $all): self
    {
        $this->all = $all;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductsId(): array
    {
        return $this->productsId;
    }

    /**
     * @param array $productsId
     */
    public function setProductsId(array $productsId): self
    {
        $this->productsId = $productsId;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

}
