<?php

namespace App\Model;

use App\Model\Base\MercuryTask as BaseMercuryTask;

/**
 * Skeleton subclass for representing a row from the 'mercury_task' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class MercuryTask extends BaseMercuryTask
{
    const
        STATUS_NEW = 1,
        STATUS_SUCCESS = 2,
        STATUS_FAILED = 3
    ;

    const
        TYPE_IMPORT_DOCUMENTS = 1,
        TYPE_EXTINGUISH = 2
    ;

    public function success(): self
    {
        $this->setStatus(self::STATUS_SUCCESS)->save();
        return $this;
    }

    public function failed(string $error = ''): self
    {
        $this
            ->setStatus(self::STATUS_FAILED)
            ->setError($error)
            ->save();

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isCancel(): bool
    {
        return in_array($this->status, [self::STATUS_SUCCESS, self::STATUS_FAILED]);
    }

    public function isExtinguishTask(): bool
    {
        return $this->type === self::TYPE_EXTINGUISH;
    }

    public function getNormalizeOptions(): array
    {
        return json_decode($this->options, true);
    }
}
