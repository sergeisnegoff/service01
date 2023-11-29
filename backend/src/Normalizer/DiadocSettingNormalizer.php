<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\DiadocSetting;

class DiadocSettingNormalizer extends AbstractNormalizer
{
    /**
     * @param DiadocSetting $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'login' => $object->getLogin(),
            'password' => $object->getPassword(),
            'apiKey' => $object->getApiKey(),
            'boxId' => $object->getBoxId(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof DiadocSetting;
    }
}
