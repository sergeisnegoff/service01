<?php

namespace App\Controller\Api;

use App\Normalizer\CompanyNormalizer;
use App\Normalizer\CompanyOrganizationShopNormalizer;
use App\Service\Buyer\BuyerList\BuyerListContext;
use App\Service\Buyer\BuyerOrganizationData\BuyerOrganizationShopData;
use App\Service\Buyer\BuyerOrganizationService;
use App\Service\Buyer\BuyerService;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\Company\CompanyService;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\Smart\Exception\SmartRequestException;
use App\Service\Smart\SmartShopImportService;
use App\Service\Smart\SmartShopService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @Route("/buyers")
 */
class BuyerController extends AbstractController
{
    /**
     * Список покупателей
     *
     * @QueryParameter("query", type="string", description="Поиск")
     * @QueryParameter("favorite", type="boolean", description="Избранное")
     * @QueryParameter("myBuyers", type="boolean", description="Мои покупатели")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("", methods={"GET"})
     */
    public function getBuyers(
        RestHandler $handler,
        BuyerService $buyerService,
        ListConfiguration $configuration,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $context = new BuyerListContext();
        $context
            ->setQuery($request->query->get('query', ''))
            ->setFavorite($request->query->getBoolean('favorite'))
            ->setMyBuyers($request->query->getBoolean('myBuyers'))
            ->setCompany($companyStorage->getCompany());

        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_COMMENT);

        return $handler->response(
            $buyerService->getBuyersList($context, $configuration)
        );
    }

    /**
     * Получить компанию с торговыми точками
     *
     * @PathParameter("id", type="string", description="ID или код организации")
     *
     * @Route("/{id}/shops", methods={"GET"})
     */
    public function getCompanyWithShops(
        RestHandler $handler,
        BuyerOrganizationService $organizationService,
        CompanyService $companyService,
        ListConfiguration $configuration,
        $id
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();

        $company = $companyService->retrieveByIdOrCode($id);

        if (!$company) {
            $handler->error->send('Организация не найдена');
        }

        if (!$company->isEqualOwner($user)) {
            $handler->error->forbidden();
        }

        $handler->data->addGroup(CompanyNormalizer::GROUP_DETAIL);
        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_COMMENT);

        return $handler->response([
            'company' => $company,
            'shops' => $organizationService->getCompanyOrganizationShops($company, $configuration)
        ]);
    }

    /**
     * Получить торговые точки организации
     *
     * @QueryParameter("companyId", type="integer", description="ID организации")
     *
     * @Route("/shops", methods={"GET"})
     */
    public function getShops(
        RestHandler $handler,
        BuyerOrganizationService $organizationService,
        CompanyService $companyService,
        ListConfiguration $configuration,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $company = $companyService->retrieveById($request->query->getInt('companyId'));
        $myCompany = $companyStorage->getCompany();

        if (!$company) {
            $handler->error->send('Организация не найдена');
        }

        $handler->data->context->set('company', $company);
        $handler->data->context->set('myCompany', $myCompany);

        return $handler->response($organizationService->getCompanyOrganizationShops($company, $configuration));
    }

    /**
     * Добавить организацию в мою компанию (покупатель)
     *
     * @RequestParameter("code", type="string", description="ID / Code организации для изменения")
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("inn", type="string", description="Инн")
     * @RequestParameter("kpp", type="string", description="Кпп")
     *
     * @Route("/self/organizations", methods={"POST"})
     */
    public function createOrganizations(
        RestHandler $handler,
        DataObjectBuilder $dataObjectBuilder,
        BuyerOrganizationService $organizationService,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $organizations = $request->request->get('organizations', []);
        $company = $companyStorage->getCompany();

        $organization = $organizationService->createOrganizations($company, $organizations);

        return $handler->response($organization);
    }

    /**
     * Создание торговых точек
     *
     * @Route("/organizations/shops", methods={"POST"})
     */
    public function createShops(RestHandler $handler, BuyerOrganizationService $organizationService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();

        $request = $handler->getRequest();
        $shops = $request->request->get('shops', []);
        $handler->data->addGroup(CompanyOrganizationShopNormalizer::GROUP_MASS_ADDITION);
        $handler->data->context->set('company', $companyStorage->getCompany());

        return $handler->response($organizationService->createShops($companyStorage->getCompany(), $shops));
    }

    /**
     * Получить торговые точки (покупатель)
     *
     * @Route("/self/organizations/shops", methods={"GET"})
     */
    public function getOrganizationShops(
        RestHandler $handler,
        BuyerOrganizationService $organizationService,
        ListConfiguration $configuration,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $handler->data->addGroup(CompanyOrganizationShopNormalizer::GROUP_DETAIL);

        return $handler->response($organizationService->getCompanyOrganizationShops(
            $companyStorage->getCompany(),
            $configuration
        ));
    }

    /**
     * Добавить торговую точку (покупатель)
     *
     * @RequestParameter("diadocExternalCode", type="string", description="Внешний код из Диадок")
     * @RequestParameter("docrobotExternalCode", type="string", description="Внешний код из Докробот")
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("code", type="string", description="Внешний код торговой точки")
     * @RequestParameter("address", type="string", description="Адрес")
     * @RequestParameter("latitude", type="string", description="Широта")
     * @RequestParameter("longitude", type="string", description="Долгота")
     *
     * @Route("/self/organizations/shops", methods={"POST"})
     */
    public function appendOrganizationShops(
        RestHandler $handler,
        DataObjectBuilder $dataObjectBuilder,
        BuyerOrganizationService $organizationService,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'title' => [new NotBlank()],
                'address' => [new NotBlank()],
                'latitude' => [new NotBlank()],
                'longitude' => [new NotBlank()],
            ],
        ]);

        $request = $handler->getRequest();
        $user = $this->getUser();
        $handler->checkPermission('buyer', $user);
        $company = $companyStorage->getCompany();

        $data = $dataObjectBuilder->build(BuyerOrganizationShopData::class, $request->request->all());
        $shop = $request->request->get('code') ? $organizationService->getShopById($request->request->get('code')) : null;

        if (!$shop) {
            $shop = $organizationService->createOrganizationShop($company, $data);

        } else {
            $organizationService->editOrganizationShop($shop, $data);
        }

        $handler->data->addGroup(CompanyOrganizationShopNormalizer::GROUP_DETAIL);

        return $handler->response($shop);
    }

    /**
     * Импорт торговых точек из SMART
     *
     * @Route("/self/organizations/shops/smart/import", methods={"POST"})
     */
    public function importSmartOrganizationShops(
        RestHandler $handler,
        SmartShopImportService $shopImportService
    ) {
        $handler->checkAuthorization();

        try {
            $user = $this->getUser();
            $shopImportService->importShops($user);
            $user->completeFirstImportSmart();

            return $handler->response(['success' => true]);

        } catch (SmartRequestException $exception) {
            $handler->error->send($exception->getMessage());
        }
    }

    /**
     * Одобрить торговые точки из SMART
     *
     * @RequestParameter("companies[]", type="array", description="Массив компаний с типом [{ companyId: 1, type: 1 }]")
     * @RequestParameter("shopIds[]", type="array", description="Массив ID торговых точек из SMART")
     *
     * @Route("/self/organizations/shops/smart/approve", methods={"POST"})
     */
    public function approveSmartOrganizationShops(
        RestHandler $handler,
        SmartShopService $smartShopService
    ) {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
//                'shopIds' => [new NotBlank(), new Type(['type' => 'array'])],
                'companies' => [new NotBlank(), new Type(['type' => 'array'])],
            ],
        ]);

        $request = $handler->getRequest();

        return $handler->response($smartShopService->approve(
            $request->request->get('companies'),
            $request->request->get('shopIds', []))
        );
    }

    /**
     * Изменить торговую точку (покупатель)
     *
     * @PathParameter("id", type="integer", description="ID торговой точки")
     *
     * @RequestParameter("diadocExternalCode", type="string", description="Внешний код из Диадок")
     * @RequestParameter("docrobotExternalCode", type="string", description="Внешний код из Докробот")
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("address", type="string", description="Адрес")
     * @RequestParameter("latitude", type="string", description="Широта")
     * @RequestParameter("longitude", type="string", description="Долгота")
     *
     * @Route("/self/organizations/shops/{id}", methods={"PUT"})
     */
    public function editOrganizationShops(
        RestHandler $handler,
        DataObjectBuilder $dataObjectBuilder,
        BuyerOrganizationService $organizationService,
        ActiveCompanyStorage $companyStorage,
        $id
    ) {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'title' => [new NotBlank()],
                'address' => [new NotBlank()],
                'latitude' => [new NotBlank()],
                'longitude' => [new NotBlank()],
            ],
        ]);

        $request = $handler->getRequest();
        $shop = $organizationService->getShopById($id);
        $user = $this->getUser();
        $handler->checkPermission('buyer', $user);
        $company = $companyStorage->getCompany();

        if (!$shop || $shop->getCompanyId() != $company->getId()) {
            $handler->error->notFound();
        }

        $data = $dataObjectBuilder->build(BuyerOrganizationShopData::class, $request->request->all());
        $handler->data->addGroup(CompanyOrganizationShopNormalizer::GROUP_DETAIL);

        return $handler->response($organizationService->editOrganizationShop($shop, $data));
    }

    /**
     * Удалить торговую точку (покупатель)
     *
     * @PathParameter("id", type="integer", description="ID торговой точки")
     * @Route("/self/organizations/shops/{id}", methods={"DELETE"})
     */
    public function deleteOrganizationShops(
        RestHandler $handler,
        BuyerOrganizationService $organizationService,
        ActiveCompanyStorage $companyStorage,
        $id
    ) {
        $handler->checkAuthorization();
        $shop = $organizationService->getShopById($id);

        $user = $this->getUser();
        $handler->checkPermission('buyer', $user);

        $company = $companyStorage->getCompany();

        if (!$shop || $shop->getCompanyId() != $company->getId()) {
            $handler->error->notFound();
        }

        $organizationService->deleteShop($shop);

        return $handler->response(['success' => true]);
    }

    /**
     * Задать альтернативное название торговой точке
     *
     * @PathParameter("id", type="integer", description="ID торговой точки")
     * @RequestParameter("alternativeTitle", type="string", description="Название")
     *
     * @Route("/organizations/shops/{id}/alternativeTitle", methods={"PUT"})
     */
    public function setAlternativeTitleShops(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        BuyerOrganizationService $buyerOrganizationService,
        $id
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();
        $user = $this->getUser();

        $handler->checkPermission('supplier', $user);
        $handler->checkFound($shop = $buyerOrganizationService->getShopById($id));

        return $handler->response(
            $buyerOrganizationService->setAlternativeTitleShop(
                $company,
                $shop,
                $request->request->get('alternativeTitle', '')
            )
        );
    }
}
