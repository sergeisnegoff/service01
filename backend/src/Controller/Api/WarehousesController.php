<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Normalizer\WarehouseNormalizer;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\Warehouse\WarehouseService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @Route("/warehouses")
 */
class WarehousesController extends AbstractController
{
    /**
     * Список складов
     *
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/self", methods={"GET"})
     */
    public function getList(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        WarehouseService $warehouseService,
        ListConfiguration $configuration
    ) {
        $handler->checkAuthorization();
        return $handler->response($warehouseService->getList($companyStorage->getCompany(), $configuration));
    }

    /**
     * Создать/изменить склад
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("code", type="string", description="Код")
     *
     * @Route("/self", methods={"POST"})
     */
    public function createWarehouses(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        WarehouseService $warehouseService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $warehouses = $request->request->get('warehouses', []);

        if (!$warehouses) {
            $handler->validate([
                'request' => [
                    'title' => [new NotBlank()],
                    'code' => [new NotBlank(), new Length(['max' => 32])],
                ]
            ]);

            $title = $request->request->get('title');
            $code = $request->request->get('code');
            $warehouse = $code ? $warehouseService->retrieve($code) : null;

            if (!$warehouse) {
                $warehouse = $warehouseService->create($companyStorage->getCompany(), $title, $code);

            } else {
                $warehouseService->edit($warehouse, $title, $code);
            }
        } else {
            $handler->data->addGroup(WarehouseNormalizer::GROUP_MASS_ADDITION);
            $warehouse = $warehouseService->createWarehouses($companyStorage->getCompany(), $warehouses);
        }

        return $handler->response($warehouse);
    }
}
