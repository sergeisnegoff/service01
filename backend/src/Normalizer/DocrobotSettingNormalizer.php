<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\DocrobotSetting;

class DocrobotSettingNormalizer extends AbstractNormalizer
{
    /**
     * @param DocrobotSetting $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'login' => $object->getLogin(),
            'password' => $object->getPassword(),
            'gln' => $object->getGln(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof DocrobotSetting;
    }
}
