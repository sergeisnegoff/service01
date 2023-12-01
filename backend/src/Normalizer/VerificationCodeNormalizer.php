<?php


namespace App\Normalizer;


use Creonit\VerificationCodeBundle\Model\VerificationCode;

class VerificationCodeNormalizer extends AbstractNormalizer
{

    /**
     * @var VerificationCode $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'key' => $object->getKey(),
            'code' => $object->getCode(),
            'createdAt' => $object->getCreatedAt(),
            'expiredAt' => $object->getExpiredAt(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof VerificationCode;
    }
}
