<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\ImportHelper;
use App\Model\Company;
use App\Model\CompanyQuery;
use App\Service\Iiko\IikoImportService;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IikoImportCommand extends Command
{
    private IikoImportService $iikoImportService;

    public function __construct(string $name = null, IikoImportService $iikoImportService)
    {
        parent::__construct($name);
        $this->iikoImportService = $iikoImportService;
    }

    protected function configure()
    {
        $this
            ->setName('iiko:import')
            ->setDescription('Импорт данных из Айко для покупателей')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ImportHelper::initImportOptions();

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $companies = CompanyQuery::create()
            ->filterByType(Company::TYPE_BUYER)
            ->useIikoSettingQuery()
                ->filterByLogin('', Criteria::NOT_EQUAL)
            ->endUse()
            ->distinct()
            ->find();

        $progress = $io->createProgressBar($companies->count());

        foreach ($companies as $company) {
            $this->iikoImportService->processFullImport($company->getIikoSetting());
            $progress->advance();
        }

        $progress->finish();
        $io->success('END');

        return Command::SUCCESS;
    }
}
