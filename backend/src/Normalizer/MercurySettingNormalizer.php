<?php
declare(strict_types=1);

namespace App\Normalizer;

use App\Model\MercurySetting;

class MercurySettingNormalizer extends AbstractNormalizer
{
    /**
     * @param MercurySetting $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'issuerId' => $object->getIssuerId(),
            'login' => $object->getLogin(),
            'veterinaryLogin' => $object->getVeterinaryLogin(),
            'password' => $object->getPassword(),
            'apiKey' => $object->getApiKey(),
            'isAutoRepayment' => $object->isAutoRepayment(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof MercurySetting;
    }
}
