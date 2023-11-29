<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\MercuryTask;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\Invoice\InvoiceService;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\Mercury\Exception\MercuryNotFoundSupplierException;
use App\Service\Mercury\MercuryData\MercurySettingsData;
use App\Service\Mercury\MercuryDoctorService;
use App\Service\Mercury\MercuryDocumentList\MercuryDocumentListContext;
use App\Service\Mercury\MercuryDocumentListService;
use App\Service\Mercury\MercuryProblemService;
use App\Service\Mercury\MercurySettingsService;
use App\Service\Mercury\MercuryTaskService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @Route("/mercuries")
 */
class MercuryController extends AbstractController
{
    /**
     * Список ВСД
     *
     * @QueryParameter("query", type="string", description="Поиск")
     * @QueryParameter("sender", type="string", description="Отправитель")
     * @QueryParameter("status", type="string", description="Статус")
     * @QueryParameter("issueDate", type="string", description="Дата")
     *
     * @QueryParameter("sortBy", type="string", description="Поле сортировки")
     * @QueryParameter("sort", type="string", description="Направление сортировки")
     *
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/documents", methods={"GET"})
     */
    public function getDocuments(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryDocumentListService $documentListService,
        ListConfiguration $configuration
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $context = new MercuryDocumentListContext();
        $context
            ->setCompany($company)
            ->fillFromRequest($request)
        ;

        return $handler->response($documentListService->getList($context, $configuration));
    }

    /**
     * Фильтр ВСД
     *
     * @QueryParameter("page")
     *
     * @Route("/documents/filter", methods={"GET"})
     */
    public function getDocumentsFilter(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryDocumentListService $documentListService
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $context = new MercuryDocumentListContext();
        $context->setCompany($company);

        return $handler->response($documentListService->getListFilter($context));
    }

    /**
     * Записать колонки
     *
     * @RequestParameter("columns", type="string", description="Массив кодов колонок")
     *
     * @Route("/columns", methods={"POST"})
     */
    public function saveColumns(RestHandler $handler, InvoiceService $invoiceService)
    {
        $handler->checkAuthorization();
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'columns' => [new NotBlank(), new Type(['type' => 'array'])],
            ]
        ]);

        $request = $handler->getRequest();
        $user = $this->getUser();

        $invoiceService->saveColumns($user, $request->request->get('columns', []));

        return $handler->response($user);
    }

    /**
     * Получить колонки
     *
     * @Route("/columns", methods={"GET"})
     */
    public function getColumns(RestHandler $handler)
    {
        $handler->checkAuthorization();
        $handler->checkAuthorization();
        $user = $this->getUser();

        return $handler->response([
            'columns' => array_filter($user->getMercuryColumns())
        ]);
    }

    /**
     * Сообщить о проблеме с документами
     *
     * @RequestParameter("documentIds[]", type="array", description="Массив GUID ВСД")
     * @RequestParameter("reason", type="string", description="Описание проблемы")
     *
     * @Route("/documents/problems", methods={"POST"})
     */
    public function sendProblemDocuments(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryProblemService $problemService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $handler->validate([
            'request' => [
                'reason' => [new NotBlank()],
                'documentIds' => [new NotBlank(), new Type(['type' => 'array'])],
            ],
        ]);

        if (!array_filter($request->request->get('documentIds'))) {
            $handler->error->set('request/documentIds', 'Выберите документы')->send();
        }

        try {
            $problemService->processProblemDocuments(
                $companyStorage->getCompany(),
                $request->request->get('reason', ''),
                $request->request->get('documentIds'),
            );

        } catch (MercuryNotFoundSupplierException $notFoundSupplierException) {
            $handler->error->send($notFoundSupplierException->getMessage());
        }

        return $handler->response();
    }

    /**
     * Погасить ВСД
     *
     * @RequestParameter("documentIds[]", type="array", description="Массив GUID ВСД")
     * @RequestParameter("unredeemed", type="boolean", description="Погасить не погашенные")
     *
     * @Route("/documents/extinguish", methods={"POST"})
     */
    public function extinguishDocument(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryTaskService $taskService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $taskService->append(
            $company,
            MercuryTask::TYPE_EXTINGUISH,
            [
                'documentIds' => $request->request->get('documentIds', []),
                'unredeemed' => $request->request->getBoolean('unredeemed'),
            ]
        );

        return $handler->response(['success' => true]);
    }

    /**
     * Обновить список ВСД
     *
     * @Route("/documents/update", methods={"POST"})
     */
    public function updateDocuments(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryTaskService $taskService
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $taskService->append($company, MercuryTask::TYPE_IMPORT_DOCUMENTS);

        return $handler->response(['success' => true]);
    }

    /**
     * Получить статус на обновление списка ВСД
     *
     * @Route("/documents/update/status", methods={"POST"})
     */
    public function getUpdateStatusDocuments(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryTaskService $taskService
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $lastTask = $taskService->getLastImportDocumentsTask($company);

        return $handler->response([
            'state' => !$lastTask || $lastTask->isCancel()
        ]);
    }

    /**
     * Список вет. врачей
     *
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/doctors", methods={"GET"})
     */
    public function getDoctors(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryDoctorService $doctorService,
        ListConfiguration $configuration
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        return $handler->response($doctorService->getList($company, $configuration));
    }

    /**
     * Добавить вет. врача
     *
     * @RequestParameter("externalCode", type="string", description="ID хоз субьекта")
     * @RequestParameter("veterinaryEmail", type="string",description="Email")
     *
     * @Route("/doctors", methods={"POST"})
     */
    public function addDoctors(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryDoctorService $doctorService
    ) {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'externalCode' => [new NotBlank()],
                'veterinaryEmail' => [new NotBlank()],
            ]
        ]);

        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $request = $handler->getRequest();

        return $handler->response($doctorService->create(
            $company,
            $request->request->get('externalCode'),
            $request->request->get('veterinaryEmail')
        ));
    }

    /**
     * Изменить вет. врача
     *
     * @PathParameter("id", type="integer")
     * @RequestParameter("externalCode", type="string", description="ID хоз субьекта")
     * @RequestParameter("veterinaryEmail", type="string",description="Email")
     *
     * @Route("/doctors/{id}", methods={"PUT"})
     */
    public function editDoctors(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryDoctorService $doctorService,
        $id
    ) {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'externalCode' => [new NotBlank()],
                'veterinaryEmail' => [new NotBlank()],
            ]
        ]);

        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $doctor = $doctorService->retrieve($id);

        if (!$doctor || $doctor->getCompanyId() !== $company->getId()) {
            $handler->error->notFound();
        }

        $request = $handler->getRequest();

        return $handler->response($doctorService->edit(
            $doctor,
            $request->request->get('externalCode'),
            $request->request->get('veterinaryEmail')
        ));
    }

    /**
     * Удалить вет. врача
     *
     * @PathParameter("id", type="integer")
     *
     * @Route("/doctors/{id}", methods={"DELETE"})
     */
    public function deleteDoctors(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercuryDoctorService $doctorService,
        $id
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $doctor = $doctorService->retrieve($id);

        if (!$doctor || $doctor->getCompanyId() !== $company->getId()) {
            $handler->error->notFound();
        }

        $doctorService->delete($doctor);

        return $handler->response();
    }

    /**
     * Заполнить настройки
     *
     * @RequestParameter("issuerId", type="string", description="GUID")
     * @RequestParameter("login", type="string", description="Логин")
     * @RequestParameter("veterinaryLogin", type="string", description="Логин ветеринарного врача")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("apiKey", type="string", description="API ключ")
     *
     * @Route("/settings", methods={"POST"})
     */
    public function fillSettings(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        DataObjectBuilder $dataObjectBuilder,
        MercurySettingsService $settingsService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $data = $dataObjectBuilder->build(MercurySettingsData::class, $request->request->all());

        return $handler->response($settingsService->fillSettings($company, $data));
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

        return $handler->response($company->getMercurySetting());
    }

    /**
     * Изменить "Автопогашение"
     *
     * @Route("/settings/autoRepayments", methods={"POST"})
     */
    public function changeAutoRepayment(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        MercurySettingsService $settingsService
    ) {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$company->isBuyerCompany()) {
            $handler->error->forbidden();
        }

        $setting = $company->getMercurySetting();
        $settingsService->changeAutoRepayment($setting);

        return $handler->response($setting);
    }
}
