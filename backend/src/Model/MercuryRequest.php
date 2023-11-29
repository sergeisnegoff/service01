<?php

namespace App\Model;

use App\Model\Base\MercuryRequest as BaseMercuryRequest;

/**
 * Skeleton subclass for representing a row from the 'mercury_request' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class MercuryRequest extends BaseMercuryRequest
{
    const
        STATUS_NEW = 0,
        STATUS_SUCCESS = 1,
        STATUS_ERROR = 2;

    public function success(): self
    {
        $this->setStatus(self::STATUS_SUCCESS)->save();
        return $this;
    }

    public function failed(string $error = ''): self
    {
        $this
            ->setStatus(self::STATUS_ERROR)
            ->setError($error)
            ->save();
        return $this;
    }
}
