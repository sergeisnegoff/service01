<?php

declare(strict_types=1);

namespace App\Service\Mercury;

use App\Helper\MemoizationTrait;
use App\Model\Company;
use App\Model\CompanyQuery;
use App\Model\MercuryDoctor;
use App\Model\MercuryDoctorQuery;
use App\Model\MercuryProblem;
use App\Model\VeterinaryDocument;
use App\Service\Mercury\Exception\MercuryNotFoundSupplierException;
use Creonit\MailingBundle\Mailing;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MercuryProblemService
{
    use MemoizationTrait;

    private MercuryDocumentService $documentService;
    private NormalizerInterface $normalizer;
    private Mailing $mailing;
    private MailerInterface $mailer;

    public function __construct(
        MercuryDocumentService $documentService,
        NormalizerInterface $normalizer,
        Mailing $mailing,
        MailerInterface $mailer
    ) {
        $this->documentService = $documentService;
        $this->normalizer = $normalizer;
        $this->mailing = $mailing;
        $this->mailer = $mailer;
    }

    /**
     * @param ObjectCollection|VeterinaryDocument[] $collection
     */
    protected function prepareDocuments(ObjectCollection $collection): array
    {
        $data = [];

        foreach ($collection as $document) {
            $documentData = $document->getNormalizeData();

            $guid = $documentData['certifiedConsignment']['consignor']['businessEntity']['guid'];
            $normalizeDocument = $this->normalizer->normalize($document);

            if (array_key_exists($guid, $data)) {
                $data[$guid][] = $normalizeDocument;
            } else {
                $data[$guid] = [$normalizeDocument];
            }
        }

        return $data;
    }

    protected function getDoctorByGuid(string $guid): ?MercuryDoctor
    {
        return $this->memoization($guid, function () use ($guid) {
            return MercuryDoctorQuery::create()
                ->filterByExternalCode($guid)
                ->findOne();
        });
    }

    protected function create(Company $company, Company $supplier, string $reason, array $documentIds): MercuryProblem
    {
        $problem = new MercuryProblem();
        $problem
            ->setCompanyRelatedByBuyerId($company)
            ->setCompanyRelatedBySupplierId($supplier)
            ->setReason($reason)
            ->setDocuments($documentIds)
            ->save();

        return $problem;
    }

    public function processProblemDocuments(Company $company, string $reason, array $documentIds): void
    {
        $documents = $this->documentService->getVeterinaryDocumentByPks($documentIds);
        $documents = $this->prepareDocuments($documents);

        $supplierGuids = array_keys($documents);
        $notFoundSuppliers = [];

        foreach ($supplierGuids as $supplierGuid) {
            $doctor = $this->getDoctorByGuid($supplierGuid);

            if (!$doctor) {
                $notFoundSuppliers[] = $supplierGuid;
                continue;
            }

            $message = $this->mailing->buildMessage('mercuryProblem', [
                'buyerTitle' => $company->getTitle(),
                'reason' => $reason,
                'items' => $documents[$supplierGuid],
            ]);

            foreach ($doctor->getNormalizeVeterinaryEmails() as $normalizeVeterinaryEmail) {
                $message->addTo($normalizeVeterinaryEmail);
            }

            $this->create($company, $doctor->getCompany(), $reason, $documentIds);
            $this->mailer->send($message);
        }

        if ($notFoundSuppliers) {
            throw new MercuryNotFoundSupplierException(
                sprintf('Поставщик(и) %s не найден(ы) в системе', implode(', ', $notFoundSuppliers))
            );
        }
    }
}
