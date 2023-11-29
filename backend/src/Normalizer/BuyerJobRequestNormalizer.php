<?php


namespace App\Normalizer;


use App\Model\BuyerJobRequest;

class BuyerJobRequestNormalizer extends AbstractNormalizer
{

    /**
     * @var BuyerJobRequest $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'text' => $object->getText(),
            'createdAt' => $object->getCreatedAt(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof BuyerJobRequest;
    }
}
