<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Normalizer\CounterpartyNormalizer;
use App\Normalizer\WarehouseNormalizer;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\Counterparty\CounterpartyService;
use App\Service\ListConfiguration\ListConfiguration;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @Route("/counterparties")
 */
class CounterpartyController extends AbstractController
{
    /**
     * Список контрагентов
     *
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/self", methods={"GET"})
     */
    public function getSelfCounterparties(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        CounterpartyService $counterpartyService,
        ListConfiguration $configuration
    ) {
        $handler->checkAuthorization();
        return $handler->response($counterpartyService->getList($companyStorage->getCompany(), $configuration));
    }

    /**
     * Создать/изменить контрагента
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("code", type="string", description="Код")
     *
     * @Route("/self", methods={"POST"})
     */
    public function createCounterparties(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        CounterpartyService $counterpartyService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $counterparties = $request->request->get('counterparties', []);

        if (!$counterparties) {
            $handler->validate([
                'request' => [
                    'title' => [new NotBlank()],
                    'code' => [new NotBlank(), new Length(['max' => 32])],
                ]
            ]);

            $title = $request->request->get('title');
            $code = $request->request->get('code');
            $counterparty = $code ? $counterpartyService->retrieve($code) : null;

            if (!$counterparty) {
                $counterparty = $counterpartyService->create($companyStorage->getCompany(), $title, $code);

            } else {
                $counterpartyService->edit($counterparty, $title, $code);
            }
        } else {
            $handler->data->addGroup(CounterpartyNormalizer::GROUP_MASS_ADDITION);
            $counterparty = $counterpartyService->createCounterparties($companyStorage->getCompany(), $counterparties);
        }

        return $handler->response($counterparty);
    }
}
