<?php


namespace App\Normalizer;


use App\Model\ProductCategory;

class ProductCategoryNormalizer extends AbstractNormalizer
{
    const GROUP_MASS_ADDITION = 'productCategory.massAddition';

    /**
     * @var ProductCategory $object
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
            'cod' => $object->getExternalCode(),
        ];

        if (!$object->getParentId()) {
            $data['children'] = $object->getProductCategoriesRelatedById();
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ProductCategory;
    }
}
