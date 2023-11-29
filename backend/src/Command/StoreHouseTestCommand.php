<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\InvoiceQuery;
use App\Service\StoreHouse\StoreHouseInvoiceService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StoreHouseTestCommand extends Command
{
    private StoreHouseInvoiceService $invoiceService;

    public function __construct(string $name = null, StoreHouseInvoiceService $invoiceService)
    {
        parent::__construct($name);
        $this->invoiceService = $invoiceService;
    }

    protected function configure()
    {
        $this
            ->setName('sh:test')
            ->addOption('invoiceId', null, InputOption::VALUE_OPTIONAL, 'Id накладной')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $invoice = InvoiceQuery::create()->findPk($input->getOption('invoiceId'));
        $this->invoiceService->add($invoice->getCompanyRelatedByBuyerId()->getStoreHouseSetting(), $invoice);
    }
}
