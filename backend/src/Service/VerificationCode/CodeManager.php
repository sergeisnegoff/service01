<?php


namespace App\Service\VerificationCode;


use Creonit\VerificationCodeBundle\Model\VerificationCodeQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class CodeManager
{
    public function existVerifiedCode($scope, $key, $code)
    {
        $date = new \DateTime();
        $date->modify('-10 minutes');

        return VerificationCodeQuery::create()
            ->filterByVerified(true)
            ->filterByScope($scope)
            ->filterByKey($key)
            ->filterByCode($code)
            ->filterByUpdatedAt($date, Criteria::GREATER_EQUAL)
            ->exists();
    }
}
