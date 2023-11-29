<?php


namespace App\Normalizer;


use App\Model\Unit;

class UnitNormalizer extends AbstractNormalizer
{

    /**
     * @var Unit $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'fromIiko' => $object->isFromIiko(),
            'fromStoreHouse' => $object->isFromStoreHouse(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Unit;
    }
}
