<?php


namespace App\EventPublisher;


use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class EventPublisher implements ServiceSubscriberInterface
{
    const MESSAGE_TYPE = 'eventMessageType';

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $serviceLocator;
    /**
     * @var PublisherInterface
     */
    private PublisherInterface $publisher;
    /**
     * @var string
     */
    private string $jwtKey;

    public function __construct(ContainerInterface $serviceLocator, PublisherInterface $publisher, string $jwtKey)
    {
        $this->serviceLocator = $serviceLocator;
        $this->publisher = $publisher;
        $this->jwtKey = $jwtKey;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedServices()
    {
        return [
            'serializer' => SerializerInterface::class
        ];
    }

    /**
     * @param string|array $topics
     * @return string
     */
    public function getSubscriberToken($topics)
    {
        $topics = $this->normalizeTopics($topics);
        $configuration = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->jwtKey));

        return $configuration->builder()
            ->withClaim('mercure', ['subscribe' => $topics])
            ->getToken($configuration->signer(), $configuration->signingKey())
            ->toString();
    }

    public function normalizeTopics($topics)
    {
        if (!is_array($topics)) {
            $topics = [$topics];
        }

        return array_map(fn($topic) => $this->getTopicUri($topic), $topics);
    }

    /**
     * @param string|ActiveRecordInterface $subject
     * @return string
     */
    public function getTopicUri($subject): string
    {
        if (is_string($subject)) {
            return $subject;
        }

        if ($subject instanceof ActiveRecordInterface) {
            $tableMap = ($subject::TABLE_MAP)::getTableMap();

            return $tableMap::TABLE_NAME . '/' . $subject->getPrimaryKey();
        }

        throw new \Exception(
            sprintf(
                'Topic subject of %s is not supported',
                is_object($subject) ? get_class($subject) : gettype($subject)
            )
        );
    }

    public function publishMessage(Update $message)
    {
        return $this->publisher->__invoke($message);
    }

    /**
     * @param string|array $topics
     * @param $subject
     * @param array $context
     * @param bool $public
     * @param null $type
     * @param int|null $retry
     * @return Update
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function createMessage($topics, $subject, array $context = [], bool $public = false, $type = null, int $retry = null)
    {
        return new Update(
            $this->normalizeTopics($topics),
            $this->normalizeData($subject, $context),
            !$public,
            null,
            $type,
            $retry
        );
    }

    public function normalizeData($subject, $context)
    {
        $serializer = $this->serviceLocator->get('serializer');
        $data = $serializer->normalize($subject, 'json', $context);

        if ($messageType = $context[self::MESSAGE_TYPE] ?? null) {
            $data += [
                '@eventMessageType' => $messageType
            ];
        }

        return $serializer->serialize($data, 'json', $context);
    }

    /**
     * @param string|array $topics
     * @param $subject
     * @param array $context
     * @param bool $public
     * @param null $type
     * @param int|null $retry
     * @return string
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function publish($topics, $subject, array $context = [], bool $public = false, $type = null, int $retry = null): string
    {
        try {
            return $this->publishMessage(
                $this->createMessage($topics, $subject, $context, $public, $type, $retry)
            );

        } catch (ExceptionInterface $exception) {
            return false;
        }
    }
}
