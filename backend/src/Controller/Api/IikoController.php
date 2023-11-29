<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\Company\ActiveCompanyStorage;
use App\Service\Iiko\IikoImportService;
use App\Service\Iiko\IikoInvoiceService;
use App\Service\Iiko\IikoSettingService;
use App\Service\Invoice\InvoiceService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/iiko")
 */
class IikoController extends AbstractController
{
    /**
     * Заполнить настройки
     *
     * @RequestParameter("login", type="string", description="Логин")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("url", type="string", description="Адрес сервера")
     *
     * @Route("/settings", methods={"POST"})
     */
    public function fillSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        IikoSettingService $settingsService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        return $handler->response($settingsService->fillSettings(
            $company,
            $request->request->get('login', ''),
            $request->request->get('password', ''),
            $request->request->get('url', '')
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

        return $handler->response($company->getIikoSetting());
    }

    /**
     * Импорт
     *
     * @Route("/import", methods={"POST"})
     */
    public function import(RestHandler $handler, IikoImportService $iikoImportService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        try {
            $iikoImportService->processFullImport($company->getIikoSetting());

        } catch (Exception $exception) {
            $handler->error->send('Не удаётся выгрузить данные, напишите в техническую поддержку');
        }

        return $handler->response(['success' => true]);
    }

    /**
     * Отправить накладную в Iiko
     *
     * @Route("/invoice/{id}/add", methods={"POST"})
     */
    public function addInvoice(
        RestHandler $handler,
        InvoiceService $invoiceService,
        IikoInvoiceService $iikoInvoiceService,
        ActiveCompanyStorage $companyStorage,
        $id
    ) {
        $handler->checkAuthorization();
        $handler->checkFound($invoice = $invoiceService->retrieveInvoice($id));
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        try {
            $iikoInvoiceService->add($company->getIikoSetting(), $invoice);

        } catch (Exception $exception) {
            $handler->error->send('Не удаётся выгрузить накладную, напишите в техническую поддержку');
        }

        return $handler->response(['success' => true]);
    }
}
