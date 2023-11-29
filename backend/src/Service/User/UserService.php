<?php

namespace App\Service\User;

use App\Helper\PhoneHelper;
use App\Model\User;
use App\Model\UserGroupRel;
use App\Service\Company\CompanyService;
use App\Service\User\Context\FindUserContext;
use App\Service\User\Context\RegisterUserContext;
use App\Service\User\Context\UpdateUserDataContext;
use App\Service\User\Exception\UserAlreadyExistsException;
use App\Validator\Constraints\NotBlank;
use App\Validator\Constraints\Phone;
use Creonit\RestBundle\Handler\RestHandler;
use Creonit\VerificationCodeBundle\CodeManager;
use Creonit\VerificationCodeBundle\Context\CodeContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Length;
use App\Service\VerificationCode\CodeManager as CustomCodeManger;

class UserService
{
    protected UserRepository $userRepository;
    protected UserPasswordEncoderInterface $passwordEncoder;
    protected PhoneHelper $phoneHelper;
    /**
     * @var UserGroupRepository
     */
    private UserGroupRepository $userGroupRepository;
    /**
     * @var CodeManager
     */
    private CodeManager $codeManager;
    /**
     * @var CustomCodeManger
     */
    private CustomCodeManger $customCodeManager;
    /**
     * @var UserAccessTokenService
     */
    private UserAccessTokenService $accessTokenService;
    /**
     * @var CompanyService
     */
    private CompanyService $companyService;
    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        PhoneHelper $phoneHelper,
        UserGroupRepository $userGroupRepository,
        CodeManager $codeManager,
        CustomCodeManger $customCodeManager,
        UserAccessTokenService $accessTokenService,
        CompanyService $companyService,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->phoneHelper = $phoneHelper;
        $this->userGroupRepository = $userGroupRepository;
        $this->codeManager = $codeManager;
        $this->customCodeManager = $customCodeManager;
        $this->accessTokenService = $accessTokenService;
        $this->companyService = $companyService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param RegisterUserContext $registerUserContext
     * @return User
     * @throws UserAlreadyExistsException
     */
    public function registerUser(RegisterUserContext $registerUserContext)
    {
        if ($this->userRepository->getUserByPhone($this->phoneHelper->normalizePhone($registerUserContext->getPhone()))) {
            throw (new UserAlreadyExistsException())->setUsername($registerUserContext->getPhone());
        }

        $user = $this->createUserByPhone($registerUserContext->getPhone(), $registerUserContext->getPassword());
        $user->setFirstName($registerUserContext->getFullName());
        $user->save();

        return $user;
    }

    public function createUserByPhone(string $phone, string $password): User
    {
        $user = new User();
        $user->setPhone($this->phoneHelper->normalizePhone($phone));
        $this->changePassword($user, $password);

        return $user;
    }

    public function createUserByEmail(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $this->changePassword($user, $password);

        return $user;
    }

    public function changePassword(User $user, string $password): void
    {
        $user->setSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
        $password = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($password);
    }

    public function checkPassword(UserInterface $user, string $password): bool
    {
        return $this->passwordEncoder->isPasswordValid($user, $password);
    }

    public function updateUserData(User $user, UpdateUserDataContext $updateUserDataContext)
    {
        $user->setFirstName($updateUserDataContext->getFirstName());
        $user->setLastName($updateUserDataContext->getLastName());
        $user->setPhone(
            $this->phoneHelper->normalizePhone($updateUserDataContext->getPhone())
        );

        $user->save();
    }

    public function addGroup(User $user, $groupCode)
    {
        $group = $this->userGroupRepository->getGroupByName($groupCode);

        if (!$group) {
            return;
        }

        $rel = new UserGroupRel();
        $rel->setUser($user)->setUserGroup($group)->save();
    }

    public function processAuthorizationStep(RestHandler $handler, string $step)
    {
        $closure = [$this, sprintf('processAuthorizationStep%s', ucfirst($step))];

        if (!is_callable($closure)) {
            $handler->error->send('Неверный шаг');
        }

        return $closure($handler);
    }

    protected function processAuthorizationStepOne(RestHandler $handler)
    {
        $handler->validate([
            'request' => [
                'phone' => [new NotBlank(), new Phone()],
                'password' => [new NotBlank(), new Length(['min' => 6])],
            ]
        ]);

        $request = $handler->getRequest();
        $phone = $request->request->get('phone');
        $password = $request->request->get('password');

        try {
            $user = $this->userRepository->getUserByPhone($this->phoneHelper->normalizePhone($phone));
            if (!$user || !$this->checkPassword($user, $password)) {
                throw new AuthenticationException();
            }

            $handler->data->set(['success' => true]);

        } catch (\Exception $exception) {
            $handler->error->set('request/phone', 'Неправильный логин или пароль')->send();
        }
    }

    protected function processAuthorizationStepTwo(RestHandler $handler)
    {
        $this->processAuthorizationStepOne($handler);
        $handler->validate([
            'request' => [
                'code' => [new NotBlank()],
            ]
        ]);

        $request = $handler->getRequest();
        $phone = $request->request->get('phone');
        $code = $request->request->get('code');

        $context = new CodeContext($phone, 'phone');
        $context->setCode($code);

        $verified = $this->codeManager->verificationCode($context);

        if ($verified) {
            $user = $this->userRepository->getUserByPhone($this->phoneHelper->normalizePhone($phone));
            $token = $this->accessTokenService->createAccessToken($user);

            $handler->data->set([
                'user' => $user,
                'token' => $token,
                'verified' => true,
            ]);

        } else {
            $handler->data->set(['verified' => false]);
        }
    }

    public function processRestorePasswordStep(RestHandler $handler, string $step)
    {
        $closure = [$this, sprintf('processRestorePasswordStep%s', ucfirst($step))];

        if (!is_callable($closure)) {
            $handler->error->send('Неверный шаг');
        }

        return $closure($handler);
    }

    protected function processRestorePasswordStepOne(RestHandler $handler)
    {
        $handler->validate([
            'request' => [
                'phone' => [new NotBlank(), new Phone()],
            ]
        ]);

        $request = $handler->getRequest();
        $phone = $request->request->get('phone');

        if (!$this->userRepository->getUserByPhone($this->phoneHelper->normalizePhone($phone))) {
            $handler->error->set('request/phone', 'Пользователь не найден')->send();
        }

        $handler->data->set(['success' => true]);
    }

    protected function processRestorePasswordStepTwo(RestHandler $handler)
    {
        $this->processRestorePasswordStepOne($handler);
        $handler->validate([
            'request' => [
                'code' => [new NotBlank()],
            ]
        ]);

        $request = $handler->getRequest();
        $phone = $request->request->get('phone');
        $code = $request->request->get('code');

        $context = new CodeContext($phone, 'phone');
        $context->setCode($code);

        $handler->data->set(['verified' => $this->codeManager->verificationCode($context)]);
    }

    protected function processRestorePasswordStepThree(RestHandler $handler)
    {
        $this->processRestorePasswordStepOne($handler);
        $handler->validate([
            'request' => [
                'password' => [new NotBlank(), new Length(['min' => 6])],
            ],
        ]);

        $request = $handler->getRequest();
        $phone = $request->request->get('phone');
        $code = $request->request->get('code');

        $user = $this->userRepository->getUserByPhone($this->phoneHelper->normalizePhone($phone));

        if (!$this->customCodeManager->existVerifiedCode('phone', $phone, $code)) {
            $handler->error->set('request/code', 'Неверный код подтверждения')->send();
        }

        $this->changePassword($user, $request->request->get('password'));
        $user->save();

        $handler->data->set(['success' => true]);
    }

    public function getCurrentUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

    public function getUserByContext(FindUserContext $context): ?User
    {
        return $this->userRepository->getUserByContext($context);
    }

    /**
     * @param int|null $id
     *
     * @return User|array|mixed|null
     */
    public function getUserById(?int $id)
    {
        return $this->userRepository->getUserById($id);
    }
}
