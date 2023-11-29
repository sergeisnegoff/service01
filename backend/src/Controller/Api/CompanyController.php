<?php

namespace App\Controller\Api;

use App\Helper\PhoneHelper;
use App\Model\Company;
use App\Normalizer\CompanyNormalizer;
use App\Normalizer\CompanyUserNormalizer;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\Company\CompanyData\UpdateCompanyData;
use App\Service\Company\CompanyFavoriteService;
use App\Service\Company\CompanyList\CompanyListContext;
use App\Service\Company\CompanyRepository;
use App\Service\Company\CompanyService;
use App\Service\Company\CompanyUserData\CompanyUserData;
use App\Service\Company\CompanyUserService;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\User\Exception\UserAlreadyExistsException;
use App\Service\User\UserRepository;
use App\Service\User\UserService;
use App\Validator\Constraints\NotBlank;
use App\Validator\Constraints\Phone;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/companies")
 */
class CompanyController extends AbstractController
{
    /**
     * Получить информацию о моей организации
     *
     * @Route("/self", methods={"GET"})
     */
    public function getCompaniesInfo(RestHandler $handler, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();

        $handler->data->addGroup(CompanyNormalizer::GROUP_DETAIL);
        $handler->data->addGroup(CompanyNormalizer::GROUP_DETAIL_CABINET);

        return $handler->response($companyStorage->getCompany());
    }

    /**
     * Добавить компанию в избранное
     *
     * @RequestParameter("id", type="integer", description="ID организации")
     *
     * @Route("/self/favorites", methods={"POST"})
     */
    public function createCompanyFavorites(
        RestHandler $handler,
        CompanyFavoriteService $favoriteService,
        CompanyRepository $companyRepository,
        ActiveCompanyStorage $companyStorage
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $handler->checkFound($favoriteCompany = $companyRepository->findPk($request->request->get('id')));

        $company = $companyStorage->getCompany();

        if (!$favoriteService->getFavorite($company, $favoriteCompany)) {
            $favoriteService->createFavorite($company, $favoriteCompany);
        }

        return $handler->response(['success' => true]);
    }

    /**
     * Удалить компанию из избранного
     *
     * @RequestParameter("id", type="integer", description="ID организации")
     *
     * @Route("/self/favorites", methods={"DELETE"})
     */
    public function deleteCompanyFavorites(
        RestHandler $handler,
        CompanyFavoriteService $favoriteService,
        CompanyRepository $companyRepository,
        ActiveCompanyStorage $companyStorage
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $handler->checkFound($favoriteCompany = $companyRepository->findPk($request->get('id')));

        $company = $companyStorage->getCompany();

        if ($companyFavorite = $favoriteService->getFavorite($company, $favoriteCompany)) {
            $favoriteService->deleteFavorite($companyFavorite);
        }

        return $handler->response(['success' => true]);
    }

    /**
     * Мои организации
     *
     * @QueryParameter("smart", type="boolean", description="Организации из смарт")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/self/list", methods={"GET"})
     */
    public function getCompanies(RestHandler $handler, CompanyService $companyService, ListConfiguration $configuration)
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $context = (new CompanyListContext())
            ->setSmart($request->query->getBoolean('smart'))
            ->setUser($this->getUser());

        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_SMART_SHOPS);

        return $handler->response($companyService->getCompanies($context, $configuration));
    }

    /**
     * Получить пользователей в моей организации
     *
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/self/users", methods={"GET"})
     */
    public function getCompanyUsers(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        ListConfiguration $configuration,
        ActiveCompanyStorage $companyStorage
    )
    {
        $handler->checkAuthorization();
        $company = $companyStorage->getCompany();

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $handler->data->addGroup(CompanyUserNormalizer::GROUP_DETAIL);

        return $handler->response($companyUserService->getCompanyUsers($company, $configuration));
    }

    /**
     * Добавить пользователя в мою компанию
     *
     * @RequestParameter("userId", type="integer", description="Id пользователя")
     *
     * @RequestParameter("search", type="string", description="Телефон/Электронная почта")
     *
     * @RequestParameter("invite", type="boolean", description="Приглашение => true, Добавление => false")
     *
     * @Route("/self/users", methods={"POST"})
     */
    public function createCompanyUsers(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        PhoneHelper $phoneHelper,
        DataObjectBuilder $dataObjectBuilder,
        ActiveCompanyStorage $companyStorage,
        UserService $userService
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $company = $companyStorage->getCompany();

        if (!$company || !$company->isEqualOwner($user)) {
            $handler->error->forbidden();
        }

        $handler->validate([
            'request' => [
                'userId' => [new Type(['type' => 'numeric'])],
                'search' => [new NotBlank()],
            ],
        ]);

        $registerUser = $userService->getUserById($request->request->get('userId'));
        $search = $request->request->get('search');

        if ($phoneHelper->checkPhone($search) && $companyUserService->getCompanyUser($company, $search)) {
            $handler->error->set('request/phone', 'Пользователь с такими данными уже зарегистрирован')->send();
        }

        $data = $dataObjectBuilder->build(CompanyUserData::class, $request->request->all());
        $data
            ->setUser($registerUser)
            ->setInvite($request->request->getBoolean('invite'))
            ->setSearch($search)
        ;

        $handler->data->addGroup(CompanyUserNormalizer::GROUP_DETAIL);

        try {
            $companyUser = $companyUserService->processCompanyUserData($company, $data);
        } catch (UserAlreadyExistsException $e) {
            $handler->error->set('request/phone', 'Пользователь компании уже существует')->send();
        }

        $handler->data->set($companyUser);

        return $handler->response();
    }

    /**
     * Детальная информация о пользователе в моей организации
     *
     * @PathParameter("id", type="integer", description="ID пользователя")
     *
     * @Route("/self/users/{id}", methods={"GET"})
     */
    public function getCompanyUser(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        ActiveCompanyStorage $companyStorage,
        $id
    )
    {
        $handler->checkAuthorization();
        $user = $this->getUser();

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $handler->checkFound($companyUser = $companyUserService->findPk($id));
        $company = $companyStorage->getCompany();

        if ($company != $companyUser->getCompany()) {
            $handler->error->forbidden();
        }

        $handler->data->addGroup(CompanyUserNormalizer::GROUP_DETAIL);

        return $handler->response($companyUser);
    }

    /**
     * Права пользователя
     *
     * @PathParameter("id", type="integer", description="ID пользователя")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/self/users/{id}/rules", methods={"GET"})
     */
    public function getCompanyUserRules(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        ActiveCompanyStorage $companyStorage,
        ListConfiguration $configuration,
        $id
    )
    {
        $handler->checkAuthorization();

        $handler->checkFound($companyUser = $companyUserService->findPk($id));
        $company = $companyStorage->getCompany();

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        if ($company != $companyUser->getCompany()) {
            $handler->error->forbidden();
        }

        return $handler->response($companyUserService->getCompanyUserRules($companyUser));
    }

    /**
     * Изменить пользователя в моей организации
     *
     * @PathParameter("id", type="integer", description="ID пользователя")
     *
     * @RequestParameter("firstName", type="string", description="Имя")
     * @RequestParameter("email", type="string", description="Email")
     * @RequestParameter("phone", type="string", description="Телефон")
     * @RequestParameter("comment", type="string", description="Комментарий")
     * @RequestParameter("image", type="string", description="Фотография")
     *
     * @RequestParameter("active", type="boolean", description="Активен")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("confirmPassword", type="string", description="Подтверждение пароля")
     *
     * @Route("/self/users/{id}", methods={"POST"})
     */
    public function editCompanyUsers(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        DataObjectBuilder $dataObjectBuilder,
        UserRepository $userRepository,
        PhoneHelper $phoneHelper,
        ActiveCompanyStorage $companyStorage,
        $id
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $handler->checkFound($companyUser = $companyUserService->findPk($id));

        $company = $companyStorage->getCompany();

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        if ($company != $companyUser->getCompany()) {
            $handler->error->forbidden();
        }

        $password = $request->request->get('password');
        $validateData = [
            'request' => [
                'firstName' => [new NotBlank()],
                'email' => [new NotBlank(), new Email()],
                'phone' => [new NotBlank(), new Phone()],
            ],
            'files' => [
                'image' => [new Image(['maxSize' => '5M'])]
            ]
        ];

        if ($password) {
            $validateData['request']['password'] = [new NotBlank(), new Length(['min' => 6])];
            $validateData['request']['confirmPassword'] = [new EqualTo(['value' => $password])];
        }

        $phone = $phoneHelper->normalizePhone($request->request->get('phone'));

        if ($phone and $user = $userRepository->getUserByPhone($phone) and $user->getId() != $companyUser->getUserId()) {
            $handler->error->set('request/phone', 'Пользователь с такими данными уже зарегистрирован')->send();
        }

        $handler->validate($validateData);

        $data = $dataObjectBuilder->build(CompanyUserData::class, $request->request->all());
        $data->setImage($request->files->get('image') ?: $request->request->get('image'));

        $handler->data->addGroup(CompanyUserNormalizer::GROUP_DETAIL);

        return $handler->response($companyUserService->editCompanyUser($companyUser, $data));
    }

    /**
     * Удалить пользователя в моей организации
     *
     * @PathParameter("id", type="integer", description="ID пользователя")
     *
     * @Route("/self/users/{id}", methods={"DELETE"})
     */
    public function deleteCompanyUsers(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        ActiveCompanyStorage $companyStorage,
        $id
    )
    {
        $handler->checkAuthorization();

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $handler->checkFound($companyUser = $companyUserService->findPk($id));
        $company = $companyStorage->getCompany();

        if ($company != $companyUser->getCompany()) {
            $handler->error->forbidden();
        }

        $companyUserService->deleteCompanyUser($companyUser);

        return $handler->response(['success' => true]);
    }

    /**
     * Изменить активность пользователя в моей компании
     *
     * @PathParameter("id", type="integer", description="ID пользователя")
     *
     * @Route("/self/users/{id}/active", methods={"PUT"})
     */
    public function changeActiveCompanyUsers(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        ActiveCompanyStorage $companyStorage,
        $id
    )
    {
        $handler->checkAuthorization();
        $user = $this->getUser();

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $handler->checkFound($companyUser = $companyUserService->findPk($id));
        $company = $companyStorage->getCompany();

        if ($company != $companyUser->getCompany()) {
            $handler->error->forbidden();
        }

        return $handler->response($companyUserService->changeActive($companyUser));
    }

    /**
     * Добавить права
     *
     * @PathParameter("id", type="integer", description="ID пользователя")
     *
     * @RequestParameter("rules", type="array", description="Массив прав (см. модель CompanyUserRule)")
     *
     * @Route("/self/users/{id}/rules", methods={"POST"})
     */
    public function createUserRules(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        ActiveCompanyStorage $companyStorage,
        $id
    )
    {
        $handler->checkAuthorization();
        $handler->checkFound($companyUser = $companyUserService->findPk($id));

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        if ($company != $companyUser->getCompany()) {
            $handler->error->forbidden();
        }

        $handler->validate([
            'request' => [
                'rules' => [new Type(['type' => 'array'])],
            ]
        ]);

        $handler->data->addGroup(CompanyUserNormalizer::GROUP_DETAIL);

        $rule = $companyUserService->getCompanyUserRules($companyUser);

        if (!$rule) {
            $rule = $companyUserService->createUserRule(
                $companyUser,
                $request->request->get('rules', [])
            );

        } else {
            $companyUserService->editUserRule(
                $rule,
                $request->request->get('rules', [])
            );
        }

        return $handler->response($rule);
    }

    /**
     * Изменить права
     *
     * @PathParameter("id", type="integer", description="ID права")
     *
     * @RequestParameter("shopsId", type="array", description="Массив ID торговых точек")
     * @RequestParameter("rules", type="array", description="Массив прав (см. модель CompanyUserRule)")
     *
     * @Route("/self/users/rules/{id}", methods={"PUT"})
     */
    public function editUserRules(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        $id
    )
    {
        $handler->checkAuthorization();
        $handler->checkFound($rule = $companyUserService->retrieveRuleByPk($id));

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $request = $handler->getRequest();

        $handler->validate([
            'request' => [
                'shopsId' => [new NotBlank(), new Type(['type' => 'array'])],
                'rules' => [new NotBlank(), new Type(['type' => 'array'])],
            ]
        ]);

        $handler->data->addGroup(CompanyUserNormalizer::GROUP_DETAIL);

        return $handler->response($companyUserService->editUserRule(
            $rule,
            $request->request->get('rules', []),
            $request->request->get('shopsId', [])
        ));
    }

    /**
     * Удалить права
     *
     * @PathParameter("id", type="integer", description="ID права")
     *
     * @Route("/self/users/rules/{id}", methods={"DELETE"})
     */
    public function deleteUserRules(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        $id
    )
    {
        $handler->checkAuthorization();
        $handler->checkFound($rule = $companyUserService->retrieveRuleByPk($id));

        if (!$companyUserService->checkAccess($this->getUser())) {
            $handler->error->forbidden();
        }

        $companyUserService->deleteUserRule($rule);

        return $handler->response(['success' => true]);
    }

    /**
     * Выбрать компанию
     *
     * @PathParameter("id", type="integer", description="ID компании")
     *
     * @Route("/self/choose/{id}", methods={"PUT"})
     */
    public function chooseCompanies(
        RestHandler $handler,
        CompanyService $companyService,
        CompanyRepository $companyRepository,
        $id
    )
    {
        $handler->checkAuthorization();
        $user = $this->getUser();

        $company = $companyRepository->findPk($id);

        if (!$company || !$company->isVisible() || !$company->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        return $handler->response($companyService->chooseCompany($user, $company));
    }

    /**
     * Создать компанию в списке
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("inn", type="string", description="Инн")
     * @RequestParameter("typeCode", type="string", description="Код типа комании")
     *
     * @Route("/self/list", methods={"POST"})
     */
    public function createCompanies(
        RestHandler $handler,
        CompanyService $companyService,
        CompanyUserService $companyUserService
    ) {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'title' => [new NotBlank()],
                'typeCode' => [new NotBlank()],
                'inn' => [new NotBlank()],
            ],
        ]);

        $user = $this->getUser();
        $request = $handler->getRequest();

        if ($companyService->retrieveByInn($request->request->get('inn'))) {
            $handler->error->set('request/inn', 'Такая организация уже есть в сервисе. Отправить запрос на присоединение?')->send();
        }

        $company = $companyService->createCompany(
            $user,
            $request->request->get('title'),
            Company::getTypeByCode($request->request->get('typeCode')),
            $request->request->get('inn')
        );

        $userData = new CompanyUserData();
        $userData
            ->setPhone($user->getPhone())
            ->setFirstName($user->getFirstName())
        ;

        $companyUserService->processCompanyUser($company, $userData, true);

        return $handler->response();
    }

    /**
     * Отправить уведомление на присоединение к компании
     *
     * @RequestParameter("inn", type="string", description="Инн или массив ИНН")
     *
     * @Route("/self/list/join", methods={"POST"})
     */
    public function sendJoinCompanyNotification(RestHandler $handler, CompanyService $companyService)
    {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'inn' => [new NotBlank()],
            ],
        ]);

        $request = $handler->getRequest();
        $inn = $request->request->get('inn');

        if (!is_array($inn)) {
            $inn = [$inn];
        }

        $companyService->sendJoinCompaniesNotification($this->getUser(), $inn);

        return $handler->response();
    }

    /**
     * Изменить название в списке
     *
     * @PathParameter("id", type="integer", description="ID компании")
     * @RequestParameter("title", type="string", description="Название")
     *
     * @Route("/self/list/{id}", methods={"PUT"})
     */
    public function editCompaniesList(
        RestHandler $handler,
        CompanyService $companyService,
        CompanyRepository $companyRepository,
        $id
    )
    {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'title' => [new NotBlank()],
            ],
        ]);

        $user = $this->getUser();
        $request = $handler->getRequest();

        $company = $companyRepository->findPk($id);

        if (!$company || !$company->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        return $handler->response($companyService->editCompanyTitle($company, $request->request->get('title')));
    }

    /**
     * Заполнить информацию о моей компании
     *
     * @RequestParameter("diadocExternalCode", type="string", description="Внешний код из Диадок")
     * @RequestParameter("docrobotExternalCode", type="string", description="Внешний код из Докробот")
     * @RequestParameter("storehouseExternalCode", type="string", description="Внешний код из Докробот")
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("description", type="string", description="Описание")
     * @RequestParameter("inn", type="string", description="Инн")
     * @RequestParameter("kpp", type="string", description="Кпп")
     * @RequestParameter("site", type="string", description="Сайт")
     * @RequestParameter("deliveryTerm", type="string", description="Условия доставки")
     * @RequestParameter("paymentTerm", type="string", description="Условия оплаты")
     * @RequestParameter("minOrderAmount", type="string", description="Минимальная сумма заказа")
     * @RequestParameter("image", type="image", description="Логотип")
     * @RequestParameter("gallery", type="array", description="Изображения галереи")
     * @RequestParameter("deleteImagesId", type="array", description="ID Изображений галереи для удаления")
     *
     * @Route("/self", methods={"POST"})
     */
    public function fillCompany(
        RestHandler $handler,
        CompanyService $companyService,
        DataObjectBuilder $dataObjectBuilder,
        ValidatorInterface $validator,
        ActiveCompanyStorage $companyStorage
    ) {
        $user = $this->getUser();
        $request = $handler->getRequest();

        $company = $companyStorage->getCompany();

        if (!$company || !$company->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        /** @var UpdateCompanyData $data */
        $data = $dataObjectBuilder->build(UpdateCompanyData::class, $request->request->all());
        $data
            ->setLogo($request->files->get('image') ?: $request->request->get('image'))
            ->setValidator($validator)
            ->setImages($request->files->get('gallery', []))
            ->setCompany($company);

        foreach ($data->validate()->all() as $key => $error) {
            $handler->error->set(sprintf('request/%s', $key), $error);
        }

        $handler->error->send();

        if ($innCompany = $companyService->retrieveByInn($request->request->get('inn')) and $company->getId() != $innCompany->getId()) {
            $handler->error->set('request/inn', 'Организация с таким ИНН уже есть. Обратитесь в техническую поддержку.')->send();
        }

        $handler->data->addGroup(CompanyNormalizer::GROUP_DETAIL);
        $handler->data->addGroup(CompanyNormalizer::GROUP_DETAIL_CABINET);

        return $handler->response($companyService->fillCompany($company, $data));
    }

    /**
     * Удалить компанию
     *
     * @PathParameter("id", type="integer", description="ID компании")
     *
     * @Route("/self/{id}", methods={"DELETE"})
     */
    public function deleteCompanies(
        RestHandler $handler,
        CompanyService $companyService,
        CompanyRepository $companyRepository,
        $id
    )
    {
        $handler->checkAuthorization();

        $user = $this->getUser();
        $company = $companyRepository->findPk($id);

        if ($user->countCompanies() === 1) {
            $handler->error->send('Нельзя удалить последнюю команию');
        }

        if (!$company || !$company->isMainOwner($user)) {
            $handler->error->notFound();
        }

        if ($company->getId() === $user->getActiveCompanyId()) {
            $handler->error->send('Нельзя удалить выбранную команию');
        }

        $companyService->deleteCompany($company);

        return $handler->response(['success' => true]);
    }

    /**
     * Изменить владельца компании
     *
     * @PathParameter("id", type="integer", description="ID компании")
     * @RequestParameter("phone", type="string", description="Телефон нового владельца")
     *
     * @Route("/self/{id}/changeOwners", methods={"PUT"})
     */
    public function changeOwner(
        RestHandler $handler,
        CompanyService $companyService,
        CompanyRepository $companyRepository,
        UserRepository $userRepository,
        PhoneHelper $phoneHelper,
        $id
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $user = $this->getUser();
        $company = $companyRepository->findPk($id);

        if (!$company || !$company->isMainOwner($user)) {
            $handler->error->notFound();
        }

        if (!$newOwner = $userRepository->getUserByPhone($phoneHelper->normalizePhone($request->request->get('phone')))) {
            $handler->error->set('request/phone', 'Пользователь не существует')->send();
        }

        $companyService->changeCompanyOwner($company, $newOwner);

        return $handler->response(['success' => true]);
    }

    /**
     * Изменить активность комании
     *
     * @PathParameter("id", type="integer", description="ID компании")
     *
     * @Route("/self/{id}/visible", methods={"PUT"})
     */
    public function setVisibleCompanies(
        RestHandler $handler,
        CompanyService $companyService,
        CompanyRepository $companyRepository,
        $id
    )
    {
        $handler->checkAuthorization();

        $user = $this->getUser();
        $company = $companyRepository->findPk($id);

        if (!$company || !$company->isMainOwner($user)) {
            $handler->error->notFound();
        }

        if ($company->getId() === $user->getActiveCompanyId()) {
            $handler->error->send('Нельзя деактивировать выбранную команию');
        }

        $companyService->changeVisible($company);

        return $handler->response(['success' => true]);
    }

    /**
     * Детальная информация о компании
     *
     * @PathParameter("id", type="integer", description="ID компании")
     *
     * @Route("/{id}", methods={"GET"})
     */
    public function getCompany(
        RestHandler $handler,
        CompanyRepository $companyRepository,
        $id
    )
    {
        $handler->checkAuthorization();
        $handler->checkFound($company = $companyRepository->findPk($id));

        $handler->data->addGroup(CompanyNormalizer::GROUP_DETAIL);
        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_COMMENT);
        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_JOB_REQUEST);

        return $handler->response($company);
    }

    /**
     * Оставить комментарий о компании
     *
     * @PathParameter("id", type="integer", description="ID компании")
     * @RequestParameter("text", type="string", description="Комментарий")
     * @RequestParameter("code", type="string", description="Внешний код покупателя")
     *
     * @Route("/{id}/comment", methods={"POST"})
     */
    public function createCommentCompany(
        RestHandler $handler,
        CompanyRepository $companyRepository,
        CompanyService $companyService,
        ActiveCompanyStorage $companyStorage,
        $id
    )
    {
        $handler->checkAuthorization();
        $handler->checkFound($commentCompany = $companyRepository->findPk($id));
        $company = $companyStorage->getCompany();

        $request = $handler->getRequest();

        $handler->data->addGroup(CompanyNormalizer::GROUP_DETAIL);
        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_COMMENT);
        $handler->data->addGroup(CompanyNormalizer::GROUP_WITH_JOB_REQUEST);

        $editCompany = $companyService->commentCompany(
            $company,
            $commentCompany,
            $request->request->get('text', ''),
            $request->request->get('code', '')
        );

        return $handler->response($editCompany);
    }
}
