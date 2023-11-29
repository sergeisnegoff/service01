<?php


namespace App\Normalizer;


use App\Model\Product;

class ProductNormalizer extends AbstractNormalizer
{
    const GROUP_MASS_ADDITION = 'product.massAddition';

    /**
     * @var Product $object
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
            'cod' => $object->getExternalCode(),
            'nomenclature' => $object->getNomenclature(),
            'unit' => $object->getUnit(),
            'vat' => $object->getVat(),
            'article' => $object->getArticle(),
            'price' => $object->getPrice(),
            'quant' => $object->getQuant(),
            'barcode' => $object->getBarcode(),
            'brand' => $object->getProductBrand(),
            'manufacturer' => $object->getProductManufacturer(),
            'category' => $object->getProductCategory(),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Product;
    }
}
