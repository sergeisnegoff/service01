<?php


namespace App\Normalizer;


use App\Helper\PriceFormatHelper;
use App\Model\Invoice;
use App\Service\Invoice\InvoiceService;

class InvoiceNormalizer extends AbstractNormalizer
{
    public const GROUP_EXTERNAL = 'invoice.external';
    public const GROUP_DETAIL = 'invoice.detail';
    public const GROUP_COMPARISON = 'invoice.comparison';
    public const GROUP_MASS_ADDITION = 'invoice.massAddition';

    /**
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * @var Invoice $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->hasGroup($context, self::GROUP_EXTERNAL)) {
            $supplier = $object->getCompanyRelatedBySupplierId();
            $buyer = $object->getCompanyRelatedByBuyerId();
            $shop = $object->getCompanyOrganizationShop();

            $data = [
                'id' => $object->getId(),
                'cod' => $object->getExternalCode(),
                'supplierId' => $supplier ? $supplier->getId() : null,
                'supplierCod' => $supplier ? $supplier->getExternalCode() : null,
                'buyerId' => $buyer ? $buyer->getId() : null,
                'buyerCod' => $buyer ? $buyer->getExternalCode() : null,
                'shopId' => $shop ? $shop->getId() : null,
                'shopCod' => $shop ? $shop->getExternalCode() : null,
                'date' => $object->getCreatedAt(),
                'number' => $object->getNumber(),
                'products' => $object->getInvoiceProducts(),
            ];

            return $this->serializer->normalize($data, $format, $context);
        }

        if ($this->hasGroup($context, self::GROUP_MASS_ADDITION)) {
            return [
                'id' => $object->getId(),
                'cod' => $object->getExternalCode(),
            ];
        }

        $shop = $object->getCompanyOrganizationShop();

        $data = [
            'id' => $object->getId(),
            'number' => $object->getNumber() ?: $object->getId(),
            'supplier' => $object->getCompanyRelatedBySupplierId(),
            'buyer' => $object->getCompanyRelatedByBuyerId(),
            'warehouse' => $object->getWarehouse(),
            'shop' => $shop,
            'createdAt' => $object->getCreatedAt(),
            'changeAt' => $object->getUpdatedAt(),
            'totalPrice' => $object->getTotalPrice(),
            'totalPriceWithVat' => $object->getTotalPriceWithVat(),
            'acceptedTotalPrice' => $object->getAcceptedTotalPrice(),
            'cod' => $object->getExternalCode(),
            'counterparty' => $object->getCounterparty(),
            'isFullComparison' => $this->invoiceService->isFullComparison($object),
            'acceptanceStatus' => $object->getInvoiceStatusRelatedByAcceptanceStatusId(),
            'iikoSend' => $object->isIikoSend(),
            'storeHouseSend' => $object->isStoreHouseSend(),
        ];

        if ($this->hasGroup($context, self::GROUP_DETAIL)) {
            $data += [
                'dischargeStatus' => $object->getInvoiceStatusRelatedByDischargeStatusId(),
                'comment' => $object->getComment(),
                'invoiceProducts' => $object->getInvoiceProducts(),
                'acceptanceAt' => $object->getAcceptanceAt(),
                'messageSupplier' => $object->getMessageSupplier(),
            ];
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Invoice;
    }
}
