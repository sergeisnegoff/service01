<?php


namespace App\Normalizer;


use App\Model\ProductManufacturer;

class ProductManufacturerNormalizer extends AbstractNormalizer
{

    /**
     * @var ProductManufacturer $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'cod' => $object->getExternalCode(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ProductManufacturer;
    }
}
