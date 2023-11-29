<?php

namespace App\Model;

use App\Model\Base\MercuryDoctor as BaseMercuryDoctor;

/**
 * Skeleton subclass for representing a row from the 'mercury_doctor' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class MercuryDoctor extends BaseMercuryDoctor
{
    public function getNormalizeVeterinaryEmails(): array
    {
        return array_map(fn (string $email) => trim($email), explode(',', $this->veterinary_email));
    }
}
