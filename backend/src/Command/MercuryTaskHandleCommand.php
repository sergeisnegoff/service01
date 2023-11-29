<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\ImportHelper;
use App\Model\MercuryTask;
use App\Service\Mercury\MercuryTaskService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MercuryTaskHandleCommand extends Command
{
    use LockableTrait;

    private MercuryTaskService $mercuryTaskService;

    public function __construct(string $name = null, MercuryTaskService $mercuryTaskService)
    {
        parent::__construct($name);
        $this->mercuryTaskService = $mercuryTaskService;
    }

    protected function configure()
    {
        $this
            ->setName('mercury-task:handle')
            ->setDescription('Обработка задач меркурий')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ImportHelper::initImportOptions();
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription() ?? $this->getName());

        if (!$this->lock()) {
            $io->writeln('The command is already running in another process.');
            return Command::SUCCESS;
        }

        $tasks = $this->mercuryTaskService->getNewTasks();

        if ($tasks) {
            $progress = $io->createProgressBar($tasks->count());
            $functions = $this->getFunctions();

            foreach ($tasks as $task) {
                $function = $functions[$task->getType()] ?? null;

                if (is_callable($function)) {
                    $function($task);
                }

                $progress->advance();
            }

            $progress->finish();
        }

        $this->release();

        return Command::SUCCESS;
    }

    protected function getFunctions(): array
    {
        return [
            MercuryTask::TYPE_IMPORT_DOCUMENTS => [$this->mercuryTaskService, 'handleImportDocuments'],
            MercuryTask::TYPE_EXTINGUISH => [$this->mercuryTaskService, 'handleExtinguishDocuments'],
        ];
    }
}
