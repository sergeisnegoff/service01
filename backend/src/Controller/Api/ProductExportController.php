<?php


namespace App\Controller\Api;


use App\Service\Company\ActiveCompanyStorage;
use App\Service\Company\CompanyRepository;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ProductExport\ProductExportData;
use App\Service\ProductExport\ProductExportService;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/productsExport")
 */
class ProductExportController extends AbstractController
{
    /**
     * Экспорт товаров
     *
     * @QueryParameter("companyId", type="int", description="ID организации")
     * @QueryParameter("all", type="boolean", description="Экспорт всех товаров")
     * @QueryParameter("productsId", type="array", description="Массив ID товаров")
     * @QueryParameter("fields", type="array", description="Массив полей для экспорта")
     *
     * @Route("", methods={"GET"})
     */
    public function exportProducts(
        RestHandler $handler,
        ProductExportService $exportService,
        DataObjectBuilder $dataObjectBuilder,
        ActiveCompanyStorage $activeCompanyStorage,
        CompanyRepository $companyRepository
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $company = $companyRepository->findPk($request->query->get('companyId')) ?? $activeCompanyStorage->getCompany();

        $data = $dataObjectBuilder->build(ProductExportData::class, $request->query->all());
        $data
            ->setCompany($company)
            ->setAll($request->query->getBoolean('all'));

        return $handler->response([
            'file' => $exportService->export($data),
        ]);
    }

    /**
     * Получить поля для экспорта
     *
     * @Route("/fields", methods={"GET"})
     */
    public function getExportFields(RestHandler $handler)
    {
        $handler->checkAuthorization();

        return $handler->response(ProductExportService::$exportFieldsCaptions);
    }
}
