<?php


namespace App\Service\Product\Exception;


class ProductDataFillException extends \Exception
{
    protected $message;
    protected $field;

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field): self
    {
        $this->field = $field;
        return $this;
    }

}
