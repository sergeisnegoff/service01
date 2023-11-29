<?php

declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\Company;
use App\Model\MercuryTask;
use App\Model\MercuryTaskQuery;
use App\Model\VeterinaryDocument;
use App\Service\Mercury\Event\MercuryTaskAppendEvent;
use App\Service\Mercury\Exception\MercuryException;
use Exception;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MercuryTaskService
{
    private MercuryService $mercuryService;
    private MercuryDocumentService $documentService;
    private MercuryDocumentExtinguishService $mercuryDocumentExtinguishService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        MercuryService $mercuryService,
        MercuryDocumentExtinguishService $mercuryDocumentExtinguishService,
        MercuryDocumentService $documentService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->mercuryService = $mercuryService;
        $this->documentService = $documentService;
        $this->mercuryDocumentExtinguishService = $mercuryDocumentExtinguishService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function append(Company $company, int $type, array $options = []): MercuryTask
    {
        $task = new MercuryTask();
        $task
            ->setCompany($company)
            ->setType($type)
            ->setOptions(json_encode($options))
            ->save();

        $this->eventDispatcher->dispatch((new MercuryTaskAppendEvent())->setTask($task), MercuryTaskAppendEvent::NAME);

        return $task;
    }

    public function getLastImportDocumentsTask(Company $company): ?MercuryTask
    {
        return MercuryTaskQuery::create()
            ->filterByCompany($company)
            ->filterByType(MercuryTask::TYPE_IMPORT_DOCUMENTS)
            ->orderByCreatedAt(Criteria::DESC)
            ->findOne();
    }

    public function getNewTasks(): ObjectCollection
    {
        return MercuryTaskQuery::create()->filterByStatus(MercuryTask::STATUS_NEW)->find();
    }

    public function handleImportDocuments(MercuryTask $task): void
    {
        $company = $task->getCompany();

        try {
            $this->mercuryService->importVeterinaryDocuments($company->getMercurySetting());
            $task->success();

        } catch (Exception $exception) {
            $task->failed($exception->getMessage());
        }
    }

    public function handleExtinguishDocuments(MercuryTask $task): void
    {
        $company = $task->getCompany();
        $options = $task->getNormalizeOptions();

        $documentIds = $options['documentIds'] ?? null;
        $unredeemed = $options['unredeemed'] ?? null;

        if (!$documentIds && !$unredeemed) {
            $task->failed();
            throw new MercuryException('Extinguish task has no options');
        }

        $documents = [];

        if ($documentIds) {
            $documents = $this->documentService->getVeterinaryDocumentByPks($documentIds);

        } else if ($unredeemed) {
            $documents = $this->documentService->getUnredeemedDocuments($company);
        }

        foreach ($documents as $document) {
            try {
                $this->mercuryDocumentExtinguishService->extinguishDocument($document);
            } catch (Exception $exception) {}
        }

        $task->success();
    }

    public function onMercuryTaskAppend(MercuryTaskAppendEvent $event)
    {
        $task = $event->getTask();

        if ($task->isExtinguishTask()) {
            $options = $task->getNormalizeOptions();
            $documentIds = $options['documentIds'] ?? null;

            if ($documentIds) {
                $this->documentService->updateTasksStatus($documentIds, VeterinaryDocument::STATUS_CODE_IN_PROCESS);
            }
        }
    }
}
