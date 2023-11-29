<?php

declare(strict_types=1);

namespace App\Helper;

class PhoneHelper
{
    public function normalizePhone($phone)
    {
        $phone = preg_replace('/^8/', '7', $phone);

        return preg_replace('/\D/', '', $phone);
    }

    public function checkPhone(string $phone): ?string
    {
        $phone = trim($phone);

        if (preg_match('/^(\+7|8|7) *(\(\d{3,4}\)|\d{3,4})([ -]*\d){6,7}$/', $phone)) {

            return $this->normalizePhone($phone);
        }

        return null;
    }
}
