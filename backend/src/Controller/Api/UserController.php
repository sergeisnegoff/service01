<?php

namespace App\Controller\Api;

use App\Helper\EmailHelper;
use App\Helper\PhoneHelper;
use App\Model\User;
use App\Normalizer\UserNormalizer;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\Company\CompanyRepository;
use App\Service\Company\CompanyService;
use App\Service\Company\CompanyUserData\CompanyUserData;
use App\Service\Company\CompanyUserService;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\User\Context\FindUserContext;
use App\Service\User\Context\RegisterUserContext;
use App\Service\User\Context\UpdateUserDataContext;
use App\Service\User\Exception\UserAlreadyExistsException;
use App\Service\User\UserAccessTokenService;
use App\Service\User\UserRepository;
use App\Service\User\UserService;
use App\Service\VerificationCode\CodeManager;
use App\Validator\Constraints\Phone;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Route("/users")
 */
class UserController extends AbstractController
{
    /**
     * Зарегистрировать пользователя
     *
     * @RequestParameter("phone", type="string", description="Телефон")
     * @RequestParameter("code", type="string", description="Код подтверждения")
     * @RequestParameter("invite", type="string", description="Хэш приглашения")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("confirmPassword", type="string", description="Повтор пароля")
     * @RequestParameter("group", type="string", description="Роль (supplier / buyer)")
     * @RequestParameter("fullName", type="string", description="ФИО")
     *
     * @Route("", methods={"POST"})
     */
    public function register(
        Request $request,
        RestHandler $handler,
        UserService $userService,
        UserAccessTokenService $accessTokenService,
        CodeManager $codeManager,
        CompanyUserService $companyUserService,
        CompanyService $companyService
    ) {
        $handler->validate([
            'request' => [
                'phone' => [new NotBlank(), new Phone()],
                'password' => [new NotBlank(['message' => 'Введите пароль']), new Length(['min' => 6])],
                'code' => [new NotBlank()],
                'fullName' => [new NotBlank()],
                'confirmPassword' => [new NotBlank(['message' => 'Введите пароль еще раз']), new EqualTo([
                    'value' => $request->request->get('password'),
                    'message' => 'Пароли не совпадают',
                ])],
            ],
        ]);

        $code = $request->request->get('code');
        $phone = $request->request->get('phone');

        if (!$codeManager->existVerifiedCode('phone', $phone, $code)) {
            $handler->error->set('request/code', 'Неверный код подтверждения')->send();
        }

        $registerUserContext = (new RegisterUserContext())
            ->setFullName($request->request->get('fullName'))
            ->setPhone($request->request->get('phone'))
            ->setPassword($request->request->get('password'));

        try {
            $user = $userService->registerUser($registerUserContext);
            $token = $accessTokenService->createAccessToken($user);

            $companyUser = $companyUserService->getByHash((string) $request->request->get('invite')) ??
                $companyUserService->getInviteUser($user->getPhone());

            $companyUserService->setRegisterUser($companyUser, $user);

        } catch (UserAlreadyExistsException $e) {
            $handler->error->set('request/phone', 'Пользователь уже существует')->send();
        }

        $handler->data->addGroup(UserNormalizer::GROUP_EVENT_SUBSCRIBER_TOKEN);

        $handler->data->set([
            'user' => $user,
            'token' => $token,
        ]);

        return $handler->response();
    }

    /**
     * Восстановить пароль
     *
     * @RequestParameter("step", type="string", description="Шаг (one, two, three)")
     * @RequestParameter("phone", type="string", description="Телефон")
     * @RequestParameter("code", type="string", description="Код")
     * @RequestParameter("password", type="string", description="Пароль")
     *
     * @Route("/restorePassword", methods={"POST"})
     */
    public function restorePassword(
        Request $request,
        RestHandler $handler,
        UserService $userService
    )
    {
        $step = $request->request->get('step', '');
        $userService->processRestorePasswordStep($handler, $step);

        return $handler->response();
    }

    /**
     * Получить токен доступа
     *
     * @RequestParameter("step", type="string", description="Шаг (one, two, three)")
     * @RequestParameter("phone", type="string", description="Телефон")
     * @RequestParameter("code", type="string", description="Код")
     * @RequestParameter("password", type="string", description="Пароль")
     *
     * @Route("/tokens", methods={"POST"})
     */
    public function createToken(
        Request $request,
        RestHandler $handler,
        UserService $userService
    )
    {
        $step = $request->request->get('step', '');
        $handler->data->addGroup(UserNormalizer::GROUP_EVENT_SUBSCRIBER_TOKEN);

        $userService->processAuthorizationStep($handler, $step);

        return $handler->response();
    }

    /**
     * Получить токен доступа для модератора
     *
     * @RequestParameter("companyId", type="integer", description="ID организации")
     *
     * @Route("/tokens/moderator", methods={"POST"})
     */
    public function createModeratorToken(RestHandler $handler, CompanyRepository $companyRepository, UserAccessTokenService $accessTokenService)
    {
        $handler->checkAuthorization();
        $handler->checkPermission('ROLE_MODERATOR');

        $request = $handler->getRequest();
        $company = $companyRepository->findPk($request->request->get('companyId'));

        if (!$company) {
            $handler->error->notFound('Компания не найдена');
        }

        $user = $company->getUserRelatedByUserId();

        return $handler->response([
            'user' => $user,
            'token' => $accessTokenService->createAccessToken($user),
        ]);
    }

    /**
     * Получить личные данные
     *
     * @Route("/self", methods={"GET"})
     */
    public function getData(RestHandler $handler)
    {
        $handler->checkAuthorization();
        $handler->data->set($this->getUser());
        $handler->data->addGroup(UserNormalizer::GROUP_EVENT_SUBSCRIBER_TOKEN);
        $handler->data->addGroup(UserNormalizer::GROUP_WITH_RULES);
        $handler->data->addGroup(UserNormalizer::GROUP_WITH_COMPANY_ACCESS_TOKEN);
        $handler->data->addGroup(UserNormalizer::GROUP_FIND_USER);


        return $handler->response();
    }

    /**
     * Изменить персональные данные
     *
     * @RequestParameter("firstName", type="string", description="Имя")
     * @RequestParameter("lastName", type="string", description="Фамилия")
     * @RequestParameter("phone", type="string", description="Телефон")
     *
     * @Route("/self", methods={"POST"})
     */
    public function updateUserData(RestHandler $handler, Request $request, UserService $userService)
    {
        $handler->checkAuthorization();

        $handler->validate([
            'request' => [
                'firstName' => [new NotBlank()],
                'lastName' => [new NotBlank()],
                'phone' => [new Phone()]
            ]
        ]);

        $updateUserDataContext = (new UpdateUserDataContext())
            ->setFirstName($request->request->get('firstName'))
            ->setLastName($request->request->get('lastName'))
            ->setPhone($request->request->get('phone'));

        $userService->updateUserData($this->getUser(), $updateUserDataContext);

        $handler->data->set($this->getUser());

        return $handler->response();
    }


    /**
     * Изменить пароль
     *
     * @RequestParameter("currentPassword", type="string", description="Текущий пароль")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("confirmPassword", type="string", description="Повтор пароля")
     *
     * @Route("/self/password", methods={"POST"})
     */
    public function changePassword(RestHandler $handler, Request $request, UserService $userService)
    {
        $handler->checkAuthorization();

        $handler->validate([
            'request' => [
                'currentPassword' => [new NotBlank(), new Length(['min' => 6])],
                'password' => [new NotBlank(), new Length(['min' => 6])],
                'confirmPassword' => [new NotBlank(), new EqualTo([
                    'value' => $request->request->get('password'),
                    'message' => 'Пароли не совпадают',
                ])],
            ]
        ]);

        $user = $this->getUser();

        if (!$userService->checkPassword($user, $request->request->get('currentPassword'))) {
            $handler->error->set('request/currentPassword', 'Неправильный пароль')->send();
        }

        $userService->changePassword($user, $request->request->get('password'));
        $user->save();

        return $handler->response();
    }

    /**
     * Поиск пользователя по телефону или email
     *
     * @QueryParameter("search", type="string", description="Телефон/Электронная почта")
     *
     * @Route("/find", methods={"GET"})
     */
    public function findUser(
        RestHandler $handler,
        UserService $userService,
        CompanyUserService $companyUserService,
        ActiveCompanyStorage $companyStorage,
        PhoneHelper $phoneHelper,
        EmailHelper $emailHelper
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        if (!$companyUserService->checkAccess($user)) {
            $handler->error->forbidden();
        }

        $company = $companyStorage->getCompany();

        if (!$company || !$company->isEqualOwner($user)) {
            $handler->error->forbidden();
        }

        $search = $request->query->get('search');

        $findUserContext = (new FindUserContext())
            ->setPhone($phoneHelper->checkPhone($search))
            ->setEmail($emailHelper->checkEmail($search));

        $handler->checkFound($user = $userService->getUserByContext($findUserContext));

        $handler->data->addGroup(UserNormalizer::GROUP_FIND_USER);
        $handler->data->set($user);

        return $handler->response();
    }

    /**
     * Изменить персональные данные сотрудника компании
     *
     * @RequestParameter("firstName", type="string", description="ФИО")
     * @RequestParameter("email", type="string", description="Email")
     * @RequestParameter("phone", type="string", description="Телефон")
     *
     * @RequestParameter("oldPassword", type="string", description="Старый пароль")
     * @RequestParameter("password", type="string", description="Пароль")
     * @RequestParameter("confirmPassword", type="string", description="Подтверждение пароля")
     *
     * @Route("/self/company", methods={"POST"})
     */
    public function editCompanyUserInfo(
        RestHandler $handler,
        CompanyUserService $companyUserService,
        DataObjectBuilder $dataObjectBuilder,
        UserRepository $userRepository,
        PhoneHelper $phoneHelper,
        UserService $userService
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        /** @var User $user */
        $user = $this->getUser();

        $password = $request->request->get('password');
        $validateData = [
            'request' => [
                'firstName' => [new \App\Validator\Constraints\NotBlank()],
                'email' => [new Email()],
                'phone' => [new NotBlank(), new Phone()],
            ]
        ];

        if ($password) {
            $validateData['request']['password'] = [new NotBlank(), new Length(['min' => 6])];
            $validateData['request']['confirmPassword'] = [
                new EqualTo(
                    ['value' => $password, 'message' => 'Пароли не совпадают']
                )
            ];

            if (!$userService->checkPassword($user, $request->request->get('oldPassword'))) {
                $handler->error->set('request/oldPassword', 'Неправильный пароль')->send();
            }

            $userService->changePassword($user, $request->request->get('password'));
        }

        $phone = $phoneHelper->normalizePhone($request->request->get('phone'));

        if ($phone and $findUser = $userRepository->getUserByPhone($phone) and $findUser->getId() != $user->getId()) {
            $handler->error->set('request/phone', 'Пользователь с такими данными уже зарегистрирован')->send();
        }

        $handler->validate($validateData);

        $data = $dataObjectBuilder->build(CompanyUserData::class, $request->request->all());

        $handler->data->addGroup(UserNormalizer::GROUP_FIND_USER);

        return $handler->response($companyUserService->editCompanyUserData($user, $data));
    }
}
