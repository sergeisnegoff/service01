<?php
declare(strict_types=1);

namespace App\Normalizer;

use App\Model\Base\Counterparty;

class CounterpartyNormalizer extends AbstractNormalizer
{
    const GROUP_MASS_ADDITION = 'counterparty.massAddition';

    /**
     * @param Counterparty $object
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
            'code' => $object->getExternalCode(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Counterparty;
    }
}
