<?php


namespace App\Normalizer;


use App\Model\InvoiceStatus;

class InvoiceStatusNormalizer extends AbstractNormalizer
{

    /**
     * @var InvoiceStatus $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'code' => $object->getCode()
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof InvoiceStatus;
    }
}
