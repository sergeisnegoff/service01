<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\Mercury\MercuryDocumentExtinguishService;
use App\Service\Mercury\MercuryDocumentService;
use App\Service\Mercury\MercuryService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportVeterinaryDocumentCommand extends Command
{
    private MercuryService $mercuryService;
    private MercuryDocumentService $documentService;
    private MercuryDocumentExtinguishService $mercuryDocumentExtinguishService;

    public function __construct(
        string $name = null,
        MercuryService $mercuryService,
        MercuryDocumentService $documentService,
        MercuryDocumentExtinguishService $mercuryDocumentExtinguishService
    ) {
        parent::__construct($name);
        $this->mercuryService = $mercuryService;
        $this->documentService = $documentService;
        $this->mercuryDocumentExtinguishService = $mercuryDocumentExtinguishService;
    }

    protected function configure()
    {
        $this
            ->setName('veterinary-document:import')
            ->setDescription('Импорт ВСД')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription() ?? $this->getName());

        $autoRepayments = $this->mercuryService->getAutoRepayments();
        $io->progressStart($autoRepayments->count());

        foreach ($autoRepayments as $autoRepayment) {
            try {
                $this->mercuryService->importVeterinaryDocuments($autoRepayment);
                $documents = $this->documentService->getUnredeemedDocuments($autoRepayment->getCompany());

                foreach ($documents as $document) {
                    $this->mercuryDocumentExtinguishService->extinguishDocument($document);
                }

            } catch (Exception $exception) {}

            $io->progressAdvance();
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }
}
