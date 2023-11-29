<?php

declare(strict_types=1);

namespace App\Helper;

class PriceFormatHelper
{
    public static function format(float $price, int $precision = 2): float
    {
        return round($price, $precision);
    }
}
