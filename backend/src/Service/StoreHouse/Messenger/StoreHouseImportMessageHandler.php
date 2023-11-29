<?php

declare(strict_types=1);

namespace App\Service\StoreHouse\Messenger;

use App\Service\StoreHouse\StoreHouseImportService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class StoreHouseImportMessageHandler implements MessageHandlerInterface
{
    private StoreHouseImportService $importService;
    private LoggerInterface $logger;

    public function __construct(StoreHouseImportService $importService, LoggerInterface $logger)
    {
        $this->importService = $importService;
        $this->logger = $logger;
    }

    public function __invoke(StoreHouseImportMessage $message)
    {
        try {
            $this->importService->processFullImport($message->getSetting());
        } catch (Exception $exception) {
            $this->logger->error(sprintf(
                'StoreHouse ERROR [%s]',
                $exception->getMessage()
            ));
        }
    }
}
