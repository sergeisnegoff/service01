<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\IikoSetting;

class IikoSettingNormalizer extends AbstractNormalizer
{
    /**
     * @param IikoSetting $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'login' => $object->getLogin(),
            'password' => $object->getPassword(),
            'url' => $object->getUrl(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof IikoSetting;
    }
}
