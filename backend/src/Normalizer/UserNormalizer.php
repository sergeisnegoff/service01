<?php


namespace App\Normalizer;


use App\EventPublisher\EventPublisher;
use App\Model\User;
use App\Service\User\UserAccessTokenService;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;

class UserNormalizer extends AbstractNormalizer
{
    const GROUP_EVENT_SUBSCRIBER_TOKEN = 'user.event_subscriber_token';
    const GROUP_WITH_RULES = 'user.rules';
    const GROUP_WITH_COMPANY_ACCESS_TOKEN = 'user.company_access_token';
    const GROUP_FIND_USER = 'user.find_user';

    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;
    private UserAccessTokenService $accessTokenService;

    public function __construct(EventPublisher $eventPublisher, UserAccessTokenService $accessTokenService)
    {
        $this->eventPublisher = $eventPublisher;
        $this->accessTokenService = $accessTokenService;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param User $object Object to normalize
     * @param string $format Format the normalization result will be encoded as
     * @param array $context Context options for the normalizer
     *
     * @return array|string|int|float|bool|\ArrayObject|null \ArrayObject is used to make sure an empty object is encoded as an object not an array
     *
     * @throws InvalidArgumentException   Occurs when the object given is not a supported type for the normalizer
     * @throws CircularReferenceException Occurs when the normalizer detects a circular reference when no circular
     *                                    reference handler can fix it
     * @throws LogicException             Occurs when the normalizer is not called in an expected context
     * @throws ExceptionInterface         Occurs for all the other cases of errors
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $company = $object->getCompanyRelatedByActiveCompanyId();

        $data = [
            'phone' => $object->getPhone(),
            'company' => $company,
            'isSupplier' => $company ? $company->isSupplierCompany() : false,
            'isBuyer' => $company ? $company->isBuyerCompany() : false,
            'isModerator' => $object->isModerator(),
            'smartShown' => $object->isSmartShown(),
        ];

        if ($this->hasGroup($context, self::GROUP_EVENT_SUBSCRIBER_TOKEN)) {
            $data += [
                'eventSubscriberToken' => $this->eventPublisher->getSubscriberToken($object)
            ];
        }

        if ($this->hasGroup($context, self::GROUP_WITH_RULES)) {
            $companyUser = $object->getCompanyUser($company);

            $data += [
                'isSubordinate' => (bool) $companyUser,
                'rules' => $companyUser ? $companyUser->getCompanyUserRules()->getFirst() : [],
            ];
        }

        if ($this->hasGroup($context, self::GROUP_FIND_USER)) {
            $data += [
                'id' => $object->getId(),
                'email' => $object->getEmail(),
                'firstName' => $object->getFirstName(),
                'middleName' => $object->getMiddleName(),
                'lastName' => $object->getLastName(),
            ];
        }

        if ($this->hasGroup($context, self::GROUP_WITH_COMPANY_ACCESS_TOKEN)) {
            if ($company && !$accessToken = $this->accessTokenService->findCompanyAccessToken($company)) {
                $accessToken = $this->accessTokenService->createAccessToken($company->getUserRelatedByUserId(), $company);
            }

            $data['accessToken'] = isset($accessToken) ? $accessToken->getToken() : '';
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed $data Data to normalize
     * @param string $format The format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof User;
    }
}
