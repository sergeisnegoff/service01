<?php


namespace App\Service\ProductImport;


use App\Service\DataObject\DataObjectInterface;

class ProductImportData implements DataObjectInterface
{
    protected string $uniqId = 'nomenclature';
    protected bool $insert = false;
    protected bool $deleteOther = false;
    protected bool $updateNomenclature = false;
    protected bool $updateUnit = false;
    protected bool $updateBarcode = false;

    /**
     * @return string
     */
    public function getUniqId(): string
    {
        return $this->uniqId;
    }

    /**
     * @param string $uniqId
     */
    public function setUniqId(string $uniqId): self
    {
        $this->uniqId = $uniqId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInsert(): bool
    {
        return $this->insert;
    }

    /**
     * @param bool $insert
     */
    public function setInsert(bool $insert): self
    {
        $this->insert = $insert;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteOther(): bool
    {
        return $this->deleteOther;
    }

    /**
     * @param bool $deleteOther
     */
    public function setDeleteOther(bool $deleteOther): self
    {
        $this->deleteOther = $deleteOther;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdateNomenclature(): bool
    {
        return $this->updateNomenclature;
    }

    /**
     * @param bool $updateNomenclature
     */
    public function setUpdateNomenclature(bool $updateNomenclature): self
    {
        $this->updateNomenclature = $updateNomenclature;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdateUnit(): bool
    {
        return $this->updateUnit;
    }

    /**
     * @param bool $updateUnit
     */
    public function setUpdateUnit(bool $updateUnit): self
    {
        $this->updateUnit = $updateUnit;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdateBarcode(): bool
    {
        return $this->updateBarcode;
    }

    /**
     * @param bool $updateBarcode
     */
    public function setUpdateBarcode(bool $updateBarcode): self
    {
        $this->updateBarcode = $updateBarcode;
        return $this;
    }

    public function getUpdateFields()
    {
        $updateFields = ProductImportService::$updateFields;

        foreach ($updateFields as $key => $updateField) {
            $property = sprintf('update%s', ucfirst($updateField));
            if (!$this->$property) {
                unset($updateFields[$key]);
            }
        }

        return $updateFields;
    }
}
