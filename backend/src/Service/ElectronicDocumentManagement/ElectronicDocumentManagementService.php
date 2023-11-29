<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement;

use App\Model\Company;
use App\Model\DiadocSetting;
use App\Model\DocrobotSetting;
use App\Service\ElectronicDocumentManagement\Diadoc\DiadocImportService;
use App\Service\ElectronicDocumentManagement\Docrobot\DocrobotImportService;
use App\Service\ElectronicDocumentManagement\Exception\ElectronicDocumentManagementImportException;
use Exception;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class ElectronicDocumentManagementService
{
    private DiadocImportService $diadocImportService;
    private DocrobotImportService $docrobotImportService;
    private RouterInterface $router;

    public function __construct(
        DiadocImportService $diadocImportService,
        DocrobotImportService $docrobotImportService,
        RouterInterface $router
    ) {
        $this->diadocImportService = $diadocImportService;
        $this->docrobotImportService = $docrobotImportService;
        $this->router = $router;
    }

    public function processImportDocuments(Company $company): void
    {
        $diadocSetting = $company->getDiadocSetting();
        $docrobotSetting = $company->getDocrobotSetting();

        if (!$diadocSetting->getLogin() && !$docrobotSetting->getLogin()) {
            throw new ElectronicDocumentManagementImportException(sprintf(
                'Накладные не могут быть получены из ЭДО. Необходимо указать данные Диадок или Докробот в разделе <a target="_blank" href="%s">интеграции</a>',
                $this->router->generate('company-integrations') . DIRECTORY_SEPARATOR
            ));
        }

        try {
            $this->importDocumentsFromDiadoc($diadocSetting);

        } catch (Exception $exception) {}

        try {
            $this->importDocumentsFromDocrobot($docrobotSetting);
        } catch (Exception $exception) {}
    }

    protected function importDocumentsFromDiadoc(DiadocSetting $setting): void
    {
        $this->diadocImportService->init($setting);
        $this->diadocImportService->processImportDocuments();
    }

    protected function importDocumentsFromDocrobot(DocrobotSetting $setting): void
    {
        $this->docrobotImportService->init($setting);
        $this->docrobotImportService->processImportDocuments();
    }
}
