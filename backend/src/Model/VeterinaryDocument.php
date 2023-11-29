<?php

namespace App\Model;

use App\Model\Base\VeterinaryDocument as BaseVeterinaryDocument;

/**
 * Skeleton subclass for representing a row from the 'veterinary_document' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class VeterinaryDocument extends BaseVeterinaryDocument
{
    const
        STATUS_CODE_CONFIRMED = 'CONFIRMED',
        STATUS_CODE_WITHDRAWN = 'WITHDRAWN',
        STATUS_CODE_UTILIZED = 'UTILIZED',
        STATUS_CODE_FINALIZED = 'FINALIZED',
        STATUS_CODE_IN_PROCESS = 'IN_PROCESS',
        STATUS_CODE_DENIED = 'DENIED'
    ;

    public static array $statusCaptions = [
        self::STATUS_CODE_CONFIRMED => 'Оформлен',
        self::STATUS_CODE_UTILIZED => 'Погашен',
        self::STATUS_CODE_WITHDRAWN => 'Аннулирован',
        self::STATUS_CODE_FINALIZED => 'Закрыт',
        self::STATUS_CODE_IN_PROCESS => 'В обработке',
        self::STATUS_CODE_DENIED => 'Отказано',
    ];

    public function isEqualOwner(Company $company): bool
    {
        return $this->company_id === $company->getId();
    }

    public function utilized(): self
    {
        $this->setStatus(self::STATUS_CODE_UTILIZED)->save();
        return $this;
    }

    public function denied(): self
    {
        $this->setStatus(self::STATUS_CODE_DENIED)->save();
        return $this;
    }

    public function getStatusCaption(): string
    {
        return self::$statusCaptions[$this->status] ?? '';
    }

    public function getNormalizeData(): array
    {
        return @json_decode($this->data, true) ?? [];
    }
}
