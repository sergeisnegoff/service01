<?php
declare(strict_types=1);

namespace App\Normalizer;

use App\Model\VeterinaryDocument;
use App\Service\Mercury\MercuryDocumentExtinguishService;

class VeterinaryDocumentNormalizer extends AbstractNormalizer
{
    private MercuryDocumentExtinguishService $mercuryDocumentExtinguishService;

    public function __construct(MercuryDocumentExtinguishService $mercuryDocumentExtinguishService)
    {
        $this->mercuryDocumentExtinguishService = $mercuryDocumentExtinguishService;
    }

    /**
     * @param VeterinaryDocument $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $documentData = $object->getNormalizeData();
        $referencedDocument = $documentData['referencedDocument'] ?? [];
        $consignment = $documentData['certifiedConsignment'] ?? [];

        $data = [
            'uuid' => $object->getUuid(),
            'waybillNumber' => $referencedDocument['issueNumber'] ?? null,
            'sender' => $object->getSender(),
            'recipient' => $object->getRecipient(),
            'status' => $object->getStatusCaption(),
            'productTitle' => $object->getProductTitle(),
            'productQuantity' => $consignment['batch']['volume'] ?? 0,
            'issueDate' => $object->getIssueDate(),
            'isAvailableExtinguish' => $this->mercuryDocumentExtinguishService->isAvailableExtinguish($object),
        ];

        return $this->serializer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof VeterinaryDocument;
    }
}
