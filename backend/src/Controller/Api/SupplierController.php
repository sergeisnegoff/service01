<?php


namespace App\Controller\Api;


use App\Normalizer\CompanyNormalizer;
use App\Service\Buyer\BuyerService;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\Company\CompanyRepository;
use App\Service\Company\CompanyVerificationRequestService;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\Supplier\SupplierList\SupplierListContext;
use App\Service\Supplier\SupplierService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/suppliers")
 */
class SupplierController extends AbstractController
{
    /**
     * Список поставщиков
     *
     * @QueryParameter("query", type="string", description="Поиск")
     * @QueryParameter("favorite", type="boolean", description="Избранное")
     * @QueryParameter("mySuppliers", type="boolean", description="Мои поставщики")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("", methods={"GET"})
     */
    public function getSuppliers(
        RestHandler $handler,
        SupplierService $supplierService,
        ListConfiguration $configuration,
        ActiveCompanyStorage $companyStorage
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $context = new SupplierListContext();
        $context
            ->setQuery($request->query->get('query', ''))
            ->setFavorite($request->query->getBoolean('favorite'))
            ->setMySuppliers($request->query->getBoolean('mySuppliers'))
            ->setCompany($companyStorage->getCompany());

        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_COMMENT);
        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_JOB_REQUEST);

        return $handler->response($supplierService->getSupplierList($context, $configuration));
    }

    /**
     * Отправить заявку на модерацию
     *
     * @Route("/self/verificationRequest", methods={"POST"})
     */
    public function createCompanyVerificationsRequest(
        RestHandler $handler,
        CompanyVerificationRequestService $companyVerificationRequestService,
        ActiveCompanyStorage $companyStorage
    )
    {
        $handler->checkAuthorization();
        $user = $this->getUser();
        $company = $companyStorage->getCompany();

        $handler->checkPermission('supplier', $user);

        if ($company->isVerified()) {
            $handler->error->send('Организация подтверждена');
        }

        if ($companyVerificationRequestService->getActiveVerificationRequest($company)) {
            $handler->error->send('Уже есть активная заявка');
        }

        foreach ($companyVerificationRequestService->validateCompanyBeforeRequest($company) as $field) {
            $handler->error->set(sprintf('request/%s', $field), 'Заполните поле');
        }

        $handler->error->send();

        return $handler->response($companyVerificationRequestService->createVerificationRequest($company));
    }

    /**
     * Отправить заявку на работу
     *
     * @PathParameter("id", type="integer", description="ID поставщика")
     * @RequestParameter("text", type="string", description="Текст заявки")
     *
     * @Route("/{id}/jobRequests", methods={"POST"})
     */
    public function sendJobRequests(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        CompanyRepository $companyRepository,
        BuyerService $buyerService,
        $id
    )
    {
        $handler->checkAuthorization();
        $user = $this->getUser();

        $handler->checkPermission('buyer', $user);
        $handler->checkFound($supplierCompany = $companyRepository->findPk($id));

        if ($buyerService->existBuyerJobRequest($companyStorage->getCompany(), $supplierCompany)) {
            $handler->error->send('Запрос уже был отправлен');
        }

        $request = $handler->getRequest();
        $buyerService->createBuyerJobRequest(
            $companyStorage->getCompany(),
            $supplierCompany,
            (string) $request->request->get('text')
        );

        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_JOB_REQUEST);

        return $handler->response($supplierCompany);
    }
}
