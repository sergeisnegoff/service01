<?php


namespace App\Normalizer;


use App\Normalizer\AbstractNormalizer;
use Creonit\PageBundle\Model\Page;
use Creonit\PageBundle\Model\PageQuery;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;

class PageNormalizer extends AbstractNormalizer
{
    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param Page $object Object to normalize
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
        $url = '';
        if ($object->isTypeRoute()) {
            try {
                $url = $this->router->generate($object->getName(), [], RouterInterface::ABSOLUTE_URL);
            } catch (\Exception $exception) {
            }

        } else {
            $url = $object->getUrl();
        }

        $typeTitle = '';

        switch ($object->getType()) {
            case Page::TYPE_LINK:
                $typeTitle = 'Ссылка';
                break;

            case Page::TYPE_ROUTE:
                $typeTitle = 'Роут';
                break;

            case Page::TYPE_MENU:
                $typeTitle = 'Меню';
                break;
            case Page::TYPE_PAGE:
                $typeTitle = 'Страница';
                break;
        }

        $data = [
            'id' => $object->getId(),
            'slug' => $object->getSlug(),
            'title' => $object->getTitle(),
            'url' => $url,
            'type' => [
                'code' => $object->getType(),
                'description' => $typeTitle,
            ],
            'createdAt' => $object->getCreatedAt(),
            'updatedAt' => $object->getUpdatedAt(),
            'meta' => [
                'title' => $object->getMetaTitle(),
                'description' => $object->getMetaDescription(),
                'keywords' => $object->getMetaKeywords(),
            ],
            'content' => $object->getContent(),
            'children' => PageQuery::create()->filterByVisible(true)->filterByHide(false)->findByParentId($object->getId()),
        ];

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
        return $data instanceof Page;
    }
}
