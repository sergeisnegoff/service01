<?php


namespace App\Normalizer;


use App\Model\CompanyVerificationRequest;

class CompanyVerificationRequestNormalizer extends AbstractNormalizer
{
    const GROUP_WITH_COMPANY = 'companyVerificationRequest.withCompany';

    /**
     * @var CompanyVerificationRequest $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'status' => $object->getStatusObject(),
            'answer' => $object->getAnswer(),
            'createdAt' => $object->getCreatedAt(),
        ];

        if ($this->hasGroup($context, self::GROUP_WITH_COMPANY)) {
            $data['company'] = $object->getCompany();
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof CompanyVerificationRequest;
    }
}
