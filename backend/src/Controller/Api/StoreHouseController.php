<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\Company\ActiveCompanyStorage;
use App\Service\StoreHouse\StoreHouseImportService;
use App\Service\StoreHouse\StoreHouseSettingService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/storeHouse")
 */
class StoreHouseController extends AbstractController
{
    /**
     * Заполнить настройки
     *
     * @RequestParameter("login", type="string", description="Логин")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("ip", type="string", description="IP")
     * @RequestParameter("port", type="string", description="Порт")
     * @RequestParameter("rid", type="string", description="RID получателя")
     *
     * @Route("/settings", methods={"POST"})
     */
    public function fillSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        StoreHouseSettingService $settingsService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if (!$company || !$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $warehouseId = $request->request->getInt('warehouseId') ?: null;

        return $handler->response($settingsService->fillSettings(
            $company,
            (string) $request->request->get('login'),
            (string) $request->request->get('password'),
            (string) $request->request->get('ip'),
            (string) $request->request->get('port'),
            (string) $request->request->get('rid'),
            $warehouseId,
        ));
    }

    /**
     * Получить настройки
     *
     * @Route("/settings", methods={"GET"})
     */
    public function getSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        return $handler->response($company->getStoreHouseSetting());
    }

    /**
     * Импорт
     *
     * @Route("/import", methods={"POST"})
     */
    public function import(RestHandler $handler, StoreHouseImportService $importService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        try {
            $importService->sendFullImportToQueue($company->getStoreHouseSetting());

        } catch (Exception $exception) {
            $handler->error->send('Не удаётся выгрузить данные, напишите в техническую поддержку');
        }

        return $handler->response(['success' => true]);
    }

}
