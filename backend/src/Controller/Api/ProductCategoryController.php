<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\Company\ActiveCompanyStorage;
use App\Service\Supplier\SupplierService;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/categories")
 */
class ProductCategoryController extends AbstractController
{
    /**
     * @Route("/delete", methods={"POST"})
     */
    public function delete(RestHandler $handler, SupplierService $supplierService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $filter = $request->request->get('filters');
        $supplierService->deleteProductCategories($companyStorage->getCompany(), $filter);

        return $handler->response();
    }
}
