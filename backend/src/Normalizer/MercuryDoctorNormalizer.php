<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\MercuryDoctor;

class MercuryDoctorNormalizer extends AbstractNormalizer
{
    /**
     * @param MercuryDoctor $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'externalCode' => $object->getExternalCode(),
            'veterinaryEmail' => $object->getVeterinaryEmail(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof MercuryDoctor;
    }
}
