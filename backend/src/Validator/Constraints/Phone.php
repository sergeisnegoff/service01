<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

class Phone extends Constraint
{
    public string $pattern = '/^(\+7|8) *(\(\d{3,4}\)|\d{3,4})([ -]*\d){6,7}$/';
    public string $message = 'Значение не является допустимым номером телефона';
}
