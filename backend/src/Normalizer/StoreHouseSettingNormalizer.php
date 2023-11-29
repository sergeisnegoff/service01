<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\StoreHouseSetting;

class StoreHouseSettingNormalizer extends AbstractNormalizer
{
    /**
     * @param StoreHouseSetting $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'login' => $object->getLogin(),
            'password' => $object->getPassword(),
            'ip' => $object->getIp(),
            'port' => $object->getPort(),
            'rid' => $object->getRid(),
            'warehouseId' => $object->getWarehouseId(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof StoreHouseSetting;
    }
}
