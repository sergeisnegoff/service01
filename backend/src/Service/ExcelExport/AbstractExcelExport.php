<?php


namespace App\Service\ExcelExport;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Propel\Runtime\Propel;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractExcelExport
{
    private $kernelProjectDir;
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem, $kernelProjectDir)
    {
        $this->kernelProjectDir = $kernelProjectDir;
        $this->filesystem = $filesystem;
    }

    public function generate($header, $body, $savePath = '', $fileName = 'export')
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        Propel::disableInstancePooling();

        $savePath = $savePath ?: $this->getSavePath();
        $this->checkPath($savePath);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->generateHeader($sheet, $header);
        $this->generateBody($sheet, $body);

        $filename = $fileName . '.xls';

        $xls = new Xls($spreadsheet);
        $xls->save($savePath . $filename);

        return $filename;
    }

    protected function generateHeader(Worksheet $worksheet, $header)
    {
        $row = 1;
        $col = 1;

        foreach ($header as $item) {
            $worksheet->setCellValueByColumnAndRow($col, $row, $item);
            $col++;
        }
    }

    protected function generateBody(Worksheet $worksheet, $body)
    {
        $row = 2;

        foreach ($body as $items) {
            $col = 1;

            foreach ($items as $item) {
                $worksheet->setCellValueByColumnAndRow($col, $row, $item);
                $col ++;
            }

            $row++;
        }
    }

    protected function checkPath($path)
    {
        if ($this->filesystem->exists($path)) {
            return;
        }

        $this->filesystem->mkdir($path);
    }

    protected function getSavePath()
    {
        return $this->kernelProjectDir . '/public/export/';
    }
}
