<?php


namespace App\Normalizer;


use App\Model\ProductImport;

class ProductImportNormalizer extends AbstractNormalizer
{
    /**
     * @var ProductImport $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'header' => $object->getHeader(),
            'rows' => $object->getRows(4),
            'map' => $object->getMapping(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ProductImport;
    }
}
