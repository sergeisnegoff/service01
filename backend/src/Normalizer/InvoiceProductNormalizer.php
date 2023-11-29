<?php


namespace App\Normalizer;


use App\Helper\PriceFormatHelper;
use App\Model\InvoiceProduct;

class InvoiceProductNormalizer extends AbstractNormalizer
{
    public const GROUP_EXTERNAL = 'invoiceProduct.external';

    /**
     * @var InvoiceProduct $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->hasGroup($context, self::GROUP_EXTERNAL)) {
            $product = $object->getProduct();
            /** @var InvoiceProduct $comparisonProduct */
            $comparisonProduct = $object->getInvoiceProductsRelatedById()->getFirst();

            $data = [
                'productId' => $product->getId(),
                'productCod' => $product->getExternalCode(),
                'price' => $object->getPrice(),
                'quantity' => $object->getQuantity(),
                'vat' => $object->getVat(),
                'amount' => $object->getTotalPriceWithVat(),
                'amountVat' => $object->getTotalPriceVat(),
                'quantityBuyer' => $comparisonProduct ? $comparisonProduct->getAcceptQuantity() : 0,
                'amountBuyer' => $comparisonProduct ? $comparisonProduct->getTotalPriceWithVat() : 0,
                'amountVatBuyer' => $comparisonProduct ? $comparisonProduct->getTotalPriceVat() : 0,
            ];

            return $this->serializer->normalize($data, $format, $context);
        }

        $data = [
            'id' => $object->getId(),
            'product' => $object->getProduct(),
            'unit' => $object->getUnit(),
            'price' => $object->getPrice(),
            'priceWithVat' => $object->getPriceWithVat(),
            'quantity' => $object->getQuantity(),
            'acceptQuantity' => $object->getAcceptQuantity(),
            'totalPrice' => $object->getTotalPrice(),
            'totalPriceWithVat' => $object->getTotalPriceWithVat(),
            'totalPriceVat' => $object->getTotalPriceVat(),
            'vat' => $object->getVat(),
        ];

        if ($this->hasGroup($context, InvoiceNormalizer::GROUP_COMPARISON)) {
            $data['comparisonProduct'] = $object->getInvoiceProductsRelatedById()->getFirst();

            if (!$object->getInvoiceId()) {
                $data['comparisonRate'] = $object->getComparisonRate();
                $data['quantityFact'] = $object->getQuantityWithComparisonRate();
                $data += [
                    'acceptedPriceWithVat' => $object->getAcceptedPriceWithVat(),
                    'acceptedPrice' => $object->getAcceptedPrice(),
                ];
            }
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof InvoiceProduct;
    }
}
