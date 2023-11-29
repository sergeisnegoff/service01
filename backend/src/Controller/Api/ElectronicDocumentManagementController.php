<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\Company\ActiveCompanyStorage;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ElectronicDocumentManagement\Diadoc\DiadocData\DiadocSettingsData;
use App\Service\ElectronicDocumentManagement\Diadoc\DiadocSettingsService;
use App\Service\ElectronicDocumentManagement\Docrobot\DocrobotData\DocrobotSettingsData;
use App\Service\ElectronicDocumentManagement\Docrobot\DocrobotSettingsService;
use App\Service\ElectronicDocumentManagement\ElectronicDocumentManagementService;
use App\Service\ElectronicDocumentManagement\Exception\ElectronicDocumentManagementImportException;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/electronicDocumentsManagement")
 */
class ElectronicDocumentManagementController extends AbstractController
{
    /**
     * Заполнить настройки диадок
     *
     * @RequestParameter("login", type="string", description="Логин")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("apiKey", type="string", description="API ключ")
     * @RequestParameter("boxId", type="string", description="ID коробки")
     *
     * @Route("/diadoc/settings", methods={"POST"})
     */
    public function fillDiadocSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        DataObjectBuilder $dataObjectBuilder,
        DiadocSettingsService $settingsService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $data = $dataObjectBuilder->build(DiadocSettingsData::class, $request->request->all());

        return $handler->response($settingsService->fillSettings($company, $data));
    }

    /**
     * Получить настройки диадок
     *
     * @Route("/diadoc/settings", methods={"GET"})
     */
    public function getDiadocSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        return $handler->response($company->getDiadocSetting());
    }

    /**
     * Заполнить настройки докробот
     *
     * @RequestParameter("login", type="string", description="Логин")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("gln", type="string", description="GLN")
     *
     * @Route("/docrobot/settings", methods={"POST"})
     */
    public function fillDocrobotSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        DataObjectBuilder $dataObjectBuilder,
        DocrobotSettingsService $settingsService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $data = $dataObjectBuilder->build(DocrobotSettingsData::class, $request->request->all());

        return $handler->response($settingsService->fillSettings($company, $data));
    }

    /**
     * Получить настройки докробот
     *
     * @Route("/docrobot/settings", methods={"GET"})
     */
    public function getDocrobotSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        return $handler->response($company->getDocrobotSetting());
    }

    /**
     * Получение накладных из ЭДО
     *
     * @Route("/import", methods={"POST"})
     */
    public function import(
        RestHandler $handler,
        ElectronicDocumentManagementService $electronicDocumentManagementService,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        try {
            $electronicDocumentManagementService->processImportDocuments($company);

        } catch (ElectronicDocumentManagementImportException $exception) {
            $handler->error->send($exception->getMessage());
        }

        return $handler->response();
    }
}
