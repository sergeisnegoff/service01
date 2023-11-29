<?php


namespace App\Service\Company\Exception;


class UpdateCompanyException extends \Exception
{
    public string $field;
    protected $message;

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField(string $field): self
    {
        $this->field = $field;
        return $this;
    }

}
