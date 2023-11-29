<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\Warehouse;

class WarehouseNormalizer extends AbstractNormalizer
{
    const GROUP_MASS_ADDITION = 'warehouse.massAddition';

    /**
     * @param Warehouse $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->hasGroup($context, self::GROUP_MASS_ADDITION)) {
            return [
                'id' => $object->getId(),
                'cod' => $object->getExternalCode(),
            ];
        }

        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'titleWithRid' => $object->getTitleWithRid(),
            'code' => $object->getExternalCode(),
            'fromIiko' => !$object->isFromStoreHouse() && $object->getExternalCode(),
            'fromStoreHouse' => $object->isFromStoreHouse(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Warehouse;
    }
}
