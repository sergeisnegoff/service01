<?php
declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\MercurySetting;
use App\Model\MercurySettingQuery;
use App\Model\User;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Propel;

class MercuryService
{
    private MercurySoapService $mercurySoapService;
    private MercuryDocumentService $documentService;

    public function __construct(MercurySoapService $mercurySoapService, MercuryDocumentService $documentService)
    {
        $this->mercurySoapService = $mercurySoapService;
        $this->documentService = $documentService;
    }

    public function getAutoRepayments(): ObjectCollection
    {
        return MercurySettingQuery::create()->filterByAutoRepayment(true)->find();
    }

    public function getAllVeterinaryDocuments(MercurySetting $setting): array
    {
        $documentsData = $this->mercurySoapService->getVeterinaryDocuments($setting);
        $out = $documentsData['items'];

        if ($offset = $documentsData['offset'] ?? null) {
            while ($offset > 0) {
                $items = $this->mercurySoapService->getVeterinaryDocuments($setting, (int) ($offset * MercurySoapService::DEFAULT_LIMIT));

                if ($documentsData['items']) {
                    $out = array_merge($out, $items['items']);
                }

                $offset--;
            }
        }

        return $out;
    }

    protected array $businessEntities = [];

    public function importVeterinaryDocuments(MercurySetting $setting): void
    {
        $connection = Propel::getConnection();
        $documentsData = $this->getAllVeterinaryDocuments($setting);

        if ($documentsData) {
            $company = $setting->getCompany();
            $existDocuments = $this->documentService->getVeterinaryDocuments($company);

            $i = 0;

            foreach ($documentsData as $documentData) {
                $enterpriseGuid = $documentData['enterpriseGuid'];

                foreach ($documentData['documents'] as $document) {
                    if (!$connection->inTransaction()) {
                        $connection->beginTransaction();
                    }

                    $businessEntityGuid = $document['certifiedConsignment']['consignor']['businessEntity']['guid'];
                    $consigneeBusinessEntityGuid = $document['certifiedConsignment']['consignee']['businessEntity']['guid'];

                    if (!isset($this->businessEntities[$businessEntityGuid])) {
                        $this->businessEntities[$businessEntityGuid] = $this->mercurySoapService->getBusinessEntityByGuid(
                            $setting,
                            $businessEntityGuid
                        );
                    }

                    if (!isset($this->businessEntities[$consigneeBusinessEntityGuid])) {
                        $this->businessEntities[$consigneeBusinessEntityGuid] = $this->mercurySoapService->getBusinessEntityByGuid(
                            $setting,
                            $consigneeBusinessEntityGuid
                        );
                    }

                    $document['sender'] = $this->businessEntities[$businessEntityGuid]['businessEntity'];
                    $document['recipient'] = $this->businessEntities[$consigneeBusinessEntityGuid]['businessEntity'];

                    if (array_key_exists($document['uuid'], $existDocuments)) {
                        $this->documentService->editVeterinaryDocument($existDocuments[$document['uuid']], $document);

                    } else {
                        $this->documentService->createVeterinaryDocument($company, $enterpriseGuid, $document);
                    }

                    $i++;

                    if ($i >= 200) {
                        $connection->commit();
                    }
                }
            }

            if ($connection->inTransaction()) {
                $connection->commit();
            }
        }
    }

    public function saveColumns(User $user, array $columns): void
    {
        $user->setMercuryColumns(array_filter($columns))->save();
    }
}
