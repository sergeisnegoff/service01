<?php


namespace App\Normalizer;


use App\Model\CompanyUser;

class CompanyUserNormalizer extends AbstractNormalizer
{
    const GROUP_DETAIL = 'companyUser.detail';

    /**
     * @var CompanyUser $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'firstName' => $object->getFirstName(),
            'active' => $object->isActive(),
            'register' => $object->isRegister(),
            'comment' => $object->getComment(),
        ];

        if ($this->hasGroup($context, self::GROUP_DETAIL)) {
            $data += [
                'image' => $object->getImage(),
                'email' => $object->getEmail(),
                'phone' => $object->getPhone(),
            ];
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof CompanyUser;
    }
}
