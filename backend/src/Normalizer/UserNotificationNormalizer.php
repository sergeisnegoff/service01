<?php


namespace App\Normalizer;


use App\Model\UserNotification;

class UserNotificationNormalizer extends AbstractNormalizer
{

    /**
     * @var UserNotification $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'id' => $object->getId(),
            'link' => $object->getLink(),
            'notification' => $object->getNotification(),
            'createdAt' => $object->getCreatedAt(),
            'read' => $object->isReaded(),
            'buyer' => $object->getCompanyRelatedByBuyerId(),
            'supplier' => $object->getCompanyRelatedBySupplierId(),
            'invoice' => $object->getInvoice(),
            'shop' => $object->getCompanyOrganizationShop(),
        ];

        $context['text'] = $object->getText();

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof UserNotification;
    }
}
