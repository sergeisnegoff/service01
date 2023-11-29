<?php

namespace App\Normalizer;

use App\Model\CompanyUserRule;

class CompanyUserRuleNormalizer extends AbstractNormalizer
{
    /**
     * @var CompanyUserRule $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'rules' => $object->getRules(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof CompanyUserRule;
    }
}
