<?php


namespace App\Controller\Api;


use App\Service\Company\ActiveCompanyStorage;
use App\Service\Unit\UnitService;
use Creonit\RestBundle\Handler\RestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/units")
 */
class UnitController extends AbstractController
{
    /**
     * Список единиц измерения
     *
     * @Route("", methods={"GET"})
     */
    public function getUnits(RestHandler $handler, UnitService $unitService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        return $handler->response($unitService->getUnitList($companyStorage->getCompany()));
    }
}
