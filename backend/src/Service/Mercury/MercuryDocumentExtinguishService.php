<?php
declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\VeterinaryDocument;
use Exception;

class MercuryDocumentExtinguishService
{
    private MercurySoapService $mercurySoapService;

    public function __construct(MercurySoapService $mercurySoapService)
    {
        $this->mercurySoapService = $mercurySoapService;
    }

    public function extinguishDocument(VeterinaryDocument $document): VeterinaryDocument
    {
        try {
            $this->mercurySoapService->extinguishVeterinaryDocument($document, $document->getCompany()->getMercurySetting());
            $document->utilized();

        } catch (Exception $exception) {
            $document->denied();
        }

        return $document;
    }

    public function isAvailableExtinguish(VeterinaryDocument $document): bool
    {
        try {
            $this->mercurySoapService->prepareExtinguishVeterinaryDocumentData(
                $document,
                $document->getCompany()->getMercurySetting(),
                1
            );

            return true;

        } catch (Exception $exception) {
            return false;
        }
    }
}
