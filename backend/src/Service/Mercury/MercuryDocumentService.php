<?php
declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\Company;
use App\Model\VeterinaryDocument;
use App\Model\VeterinaryDocumentQuery;
use Propel\Runtime\Collection\ObjectCollection;

class MercuryDocumentService
{
    public function getVeterinaryDocument($id): ?VeterinaryDocument
    {
        return VeterinaryDocumentQuery::create()->findPk($id);
    }

    public function getVeterinaryDocumentByPks(array $pks): ObjectCollection
    {
        return VeterinaryDocumentQuery::create()->findPks($pks);
    }

    public function getUnredeemedDocuments(Company $company): ObjectCollection
    {
        return VeterinaryDocumentQuery::create()
            ->filterByCompany($company)
            ->filterByStatus(VeterinaryDocument::STATUS_CODE_CONFIRMED)
            ->find();
    }

    public function getVeterinaryDocuments(Company $company): array
    {
        return VeterinaryDocumentQuery::create()
            ->filterByCompany($company)
            ->find()
            ->toKeyIndex();
    }

    public function countVeterinaryDocuments(Company $company): int
    {
        return VeterinaryDocumentQuery::create()
            ->filterByCompany($company)
            ->find()
            ->count();
    }

    public function createVeterinaryDocument(Company $company, string $enterpriseGuid, array $data): VeterinaryDocument
    {
        $document = new VeterinaryDocument();
        $document
            ->setUuid($data['uuid'])
            ->setEnterpriseGuid($enterpriseGuid)
            ->setCompany($company)
            ->setIssueDate($data['issueDate'])
            ->setStatus($data['vetDStatus'])
            ->setSender($data['sender']['name'] ?? '')
            ->setRecipient($data['recipient']['fio'] ?? '')
            ->setData(json_encode($data))
            ->setProductTitle($data['certifiedConsignment']['batch']['productItem']['name'])
        ;

        $document->save();

        return $document;
    }

    public function editVeterinaryDocument(VeterinaryDocument $document, array $data): VeterinaryDocument
    {
        $document
            ->setIssueDate($data['issueDate'])
            ->setStatus($data['vetDStatus'])
            ->setSender($data['sender']['name'] ?? '')
            ->setRecipient($data['recipient']['fio'] ?? '')
            ->setData(json_encode($data))
            ->setProductTitle($data['certifiedConsignment']['batch']['productItem']['name'])
        ;

        $document->save();

        return $document;
    }

    public function updateTasksStatus(array $ids, string $statusCode): void
    {
        VeterinaryDocumentQuery::create()
            ->filterByUuid($ids)
            ->update(['Status' => $statusCode]);
    }
}
