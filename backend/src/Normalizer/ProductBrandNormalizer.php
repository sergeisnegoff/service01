<?php


namespace App\Normalizer;


use App\Model\ProductBrand;

class ProductBrandNormalizer extends AbstractNormalizer
{

    /**
     * @var ProductBrand $object
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
        return $data instanceof ProductBrand;
    }
}
