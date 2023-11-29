<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\CompanyQuery;
use App\Service\ElectronicDocumentManagement\ElectronicDocumentManagementService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElectronicDocumentManagementImportCommand extends Command
{
    private ElectronicDocumentManagementService $documentManagementService;

    public function __construct(
        string $name = null,
        ElectronicDocumentManagementService $documentManagementService
    ) {
        parent::__construct($name);
        $this->documentManagementService = $documentManagementService;
    }

    protected function configure()
    {
        $this
            ->setName('electronic-document-management:import')
            ->setDescription('Импорт накладных из ЭДО')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $companies = CompanyQuery::create()->find();
        $progress = $io->createProgressBar($companies->count());

        foreach ($companies as $company) {
            $this->documentManagementService->processImportDocuments($company);
            $progress->advance();
        }

        $progress->finish();
        $io->success('END');

        return Command::SUCCESS;
    }
}
