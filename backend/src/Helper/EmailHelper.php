<?php

declare(strict_types=1);

namespace App\Helper;

class EmailHelper
{
    public function checkEmail(string $email): ?string
    {
        $email = trim($email);

        if (preg_match('/[0-9a-z]+@[a-z]/', $email)) {
            return $email;
        }

        return null;
    }
}
