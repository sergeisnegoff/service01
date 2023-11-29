<?php


namespace App\Service\ExcelImport;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Propel\Runtime\Propel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractExcelImport
{
    const CLEAR_CONNECTIONS_INTERVAL = 300;
    protected int $countRequests = 0;

    abstract protected function processRow(Worksheet $worksheet, int $rowNumber);

    protected array $columns = [];

    protected function getColumns(Worksheet $worksheet): array
    {
        if (!$this->columns) {
            $iterator = $worksheet->getColumnIterator('A', $worksheet->getHighestColumn());

            $i = 1;

            foreach ($iterator as $item) {
                $this->columns[] = $i++;
            }
        }

        return $this->columns;
    }

    public function process(UploadedFile $file, $sheetName = '')
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        Propel::disableInstancePooling();

        $connection = Propel::getConnection();

        if (fopen($file->getRealPath(), 'r')) {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $workSheet = $sheetName ? $spreadsheet->getSheetByName($sheetName) : $spreadsheet->getActiveSheet();

            $rows = $workSheet->getHighestRow();
            $row = 1;

            while ($row <= $rows) {
                if (!$connection->inTransaction()) {
                    $connection->beginTransaction();
                }

                try {
                    $this->processRow($workSheet, $row);

                    if ($this->countRequests > self::CLEAR_CONNECTIONS_INTERVAL || $this->countRequests >= ($rows - 1)) {
                        $connection->commit();
                        Propel::closeConnections();
                    }

                } catch (\Exception $exception) {
                    if ($connection->inTransaction()) {
                        $connection->rollBack();
                    }
                }

                $row++;
            }

            if ($connection->inTransaction()) {
                try {
                    $connection->beginTransaction();

                } catch (\Exception $exception) {
                    $connection->rollBack();
                }
            }
        }
    }

    protected function incrementCountRequests(): void
    {
        $this->countRequests++;
    }
}
