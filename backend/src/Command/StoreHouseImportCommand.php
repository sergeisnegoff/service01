<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\ImportHelper;
use App\Model\Company;
use App\Model\CompanyQuery;
use App\Service\StoreHouse\StoreHouseImportService;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StoreHouseImportCommand extends Command
{
    private StoreHouseImportService $importService;

    public function __construct(string $name = null, StoreHouseImportService $importService)
    {
        parent::__construct($name);
        $this->importService = $importService;
    }

    protected function configure()
    {
        $this
            ->setName('sh:import')
            ->setDescription('Импорт данных из SH для покупателей')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ImportHelper::initImportOptions();

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $companies = CompanyQuery::create()
            ->filterByType(Company::TYPE_BUYER)
            ->useStoreHouseSettingQuery()
                ->filterByLogin('', Criteria::NOT_EQUAL)
            ->endUse()
            ->distinct()
            ->find();

        $progress = $io->createProgressBar($companies->count());

        foreach ($companies as $company) {
            $this->importService->sendFullImportToQueue($company->getStoreHouseSetting());
            $progress->advance();
        }

        $progress->finish();
        $io->success('END');

        return Command::SUCCESS;
    }
}
