<?php


namespace App\Normalizer;


use App\Model\Notification;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;

class NotificationNormalizer extends AbstractNormalizer
{

    /**
     * @var Notification $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $text = isset($context['text']) && $context['text'] ? $context['text'] : $object->getText();

        $data = [
            'text' => $text,
            'code' => $object->getCode(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Notification;
    }
}
