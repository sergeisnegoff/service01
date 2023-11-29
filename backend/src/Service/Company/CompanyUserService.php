<?php

namespace App\Service\Company;

use App\Helper\EmailHelper;
use App\Helper\ImageHelper;
use App\Helper\PhoneHelper;
use App\Model\Company;
use App\Model\CompanyUser;
use App\Model\CompanyUserQuery;
use App\Model\CompanyUserRule;
use App\Model\CompanyUserRuleQuery;
use App\Model\User;
use App\Model\UserGroup;
use App\Service\Company\CompanyUserData\CompanyUserData;
use App\Service\Company\Event\AfterCreateCompanyUserEvent;
use App\Service\Company\Event\AfterEditCompanyUserEvent;
use App\Service\Company\Event\InviteCompanyUserEvent;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\User\Context\RegisterUserContext;
use App\Service\User\Exception\UserAlreadyExistsException;
use App\Service\User\UserRepository;
use App\Service\User\UserService;
use Creonit\MediaBundle\Service\MediaService;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyUserService
{
    /**
     * @var UserService
     */
    private UserService $userService;
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;
    /**
     * @var CompanyService
     */
    private CompanyService $companyService;
    /**
     * @var MediaService
     */
    private MediaService $mediaService;
    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;
    /**
     * @var PhoneHelper
     */
    private PhoneHelper $phoneHelper;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    private CompanyUserRepository $companyUserRepository;

    private EmailHelper $emailHelper;

    public function __construct(
        UserService $userService,
        EventDispatcherInterface $dispatcher,
        CompanyService $companyService,
        MediaService $mediaService,
        ListConfigurationService $listConfigurationService,
        PhoneHelper $phoneHelper,
        UserRepository $userRepository,
        EmailHelper $emailHelper,
        CompanyUserRepository $companyUserRepository
    )
    {
        $this->userService = $userService;
        $this->dispatcher = $dispatcher;
        $this->companyService = $companyService;
        $this->mediaService = $mediaService;
        $this->listConfigurationService = $listConfigurationService;
        $this->phoneHelper = $phoneHelper;
        $this->userRepository = $userRepository;
        $this->emailHelper = $emailHelper;
        $this->companyUserRepository = $companyUserRepository;
    }

    public function findPk($id): ?CompanyUser
    {
        return CompanyUserQuery::create()->findPk($id);
    }

    public function getCompanyUser(Company $company, string $phone): ?CompanyUser
    {
        return CompanyUserQuery::create()->filterByPhone($phone)->filterByCompany($company)->findOne();
    }

    public function getByPhone(Company $company, ?string $phone): ?CompanyUser
    {
        return CompanyUserQuery::create()->filterByCompany($company)->filterByPhone($phone)->findOne();
    }

    public function getByEmail(Company $company, ?string $email): ?CompanyUser
    {
        return CompanyUserQuery::create()->filterByCompany($company)->filterByEmail($email)->findOne();
    }

    public function getUsersByPhone(string $phone)
    {
        return CompanyUserQuery::create()->filterByPhone($phone)->find();
    }

    public function getByHash(string $hash): ?CompanyUser
    {
        return CompanyUserQuery::create()
            ->where(sprintf('MD5(CONCAT_WS(\'|\', company_user.id, company_user.created_at)) = \'%s\'', $hash))
            ->findOne();
    }

    public function getInviteUser(string $phone): ?CompanyUser
    {
        return CompanyUserQuery::create()
            ->filterByPhone($phone)
            ->filterByRegister(false)
            ->filterByUserId(null)
            ->findOne();
    }

    public function getCompanyUserByInviteData(?Company $company, string $phone): ?CompanyUser
    {
        return $this->companyUserRepository->getCompanyUserByInviteData($company, $phone);
    }

    public function setRegisterUser(?CompanyUser $companyUser, User $user)
    {
        if ($companyUser) {
            $companyUser->setUser($user);
            $companyUser->setPhone($user->getPhone());
            $companyUser->setEmail($user->getEmail());
            $companyUser->setFirstName($user->getFirstName());
            $companyUser->setRegister(true);

            $companyUser->save();
        }
    }

    public function processCompanyUser(Company $company, CompanyUserData $userData, bool $owner = false): CompanyUser
    {
        $user = $company->getUserRelatedByUserId();
        $phone = $this->phoneHelper->normalizePhone($userData->getPhone());

        if (!$registerUser = $this->userRepository->getUserByPhone($phone)) {
            $registerContext = new RegisterUserContext();
            $registerContext
                ->setPhone($phone)
                ->setPassword($userData->getPassword())
                ->setGroupCode($user->isSupplier() ? UserGroup::GROUP_SUPPLIER : UserGroup::GROUP_BUYER);

            $registerUser = $this->userService->registerUser($registerContext);
            $this->companyService->chooseCompany($registerUser, $company);
        }

        $companyUser = new CompanyUser();
        $companyUser
            ->setRegister(true)
            ->setCompany($company)
            ->setUser($registerUser)
            ->setFirstName($userData->getFirstName())
            ->setPhone($phone)
            ->setEmail($userData->getEmail())
            ->setComment($userData->getComment());

        if ($image = $userData->getImage()) {
            if ($image instanceof UploadedFile) {
                $file = $this->mediaService->uploadFile($image);
                $file = $this->mediaService->createFile($file);

                $companyUser->setImage(
                    ImageHelper::makeImage($file)
                );

            } else if ($image == null) {
                $companyUser->setImage(null);
            }
        }

        if ($owner) {
            $rule = new CompanyUserRule();
            $rule->setRules(
                $company->isBuyerCompany() ? CompanyUserRule::getBuyerRules() : CompanyUserRule::getSupplierRules()
            );

            $companyUser->addCompanyUserRule($rule);
        }

        $companyUser->save();

        $this->dispatcher->dispatch((new AfterCreateCompanyUserEvent())->setCompanyUser($companyUser));

        return $companyUser;
    }

    /**
     * @throws PropelException|UserAlreadyExistsException
     */
    public function processCompanyUserData(Company $company, CompanyUserData $userData): CompanyUser
    {
        $search = $userData->getSearch();
        $userData->setEmail($this->emailHelper->checkEmail($search));
        $userData->setPhone($this->phoneHelper->checkPhone($search));

        if ($userData->getPhone() && $this->getByPhone($company, $userData->getPhone())) {
            throw new UserAlreadyExistsException();
        }

        if ($userData->getEmail() && $this->getByEmail($company, $userData->getEmail())) {
            throw new UserAlreadyExistsException();
        }

        $user = $userData->getUser();
        $companyUser = new CompanyUser();
        $companyUser
            ->setUser($user)
            ->setCompany($company)
            ->setPhone($userData->getPhone())
            ->setEmail($userData->getEmail())
        ;

        if ($user) {
            $companyUser->setFirstName($user->getFirstName());
        }

        if (!$userData->isInvite()) {
            $companyUser->setRegister(true);
        }

        $companyUser->save();

        if ($userData->isInvite()) {
            $event = new InviteCompanyUserEvent();
            $event
                ->setCompanyUser($companyUser)
                ->setCompany($company)
                ->setUserData($userData);

            $this->dispatcher->dispatch($event);
        }

        return $companyUser;
    }

    public function editCompanyUserData(User $user, CompanyUserData $userData): User
    {
        $phone = $this->phoneHelper->normalizePhone($userData->getPhone());

        $user
            ->setFirstName($userData->getFirstName())
            ->setEmail($userData->getEmail() ?: "")
            ->setPhone($phone);

        if ($userData->getPassword()) {
            $this->userService->changePassword($user, $userData->getPassword());
        }

        if ($user->isModified()) {
            $user->save();
        }

        $companyUsers = $this->getUsersByPhone($user->getPhone());

        foreach ($companyUsers as $companyUser) {
            $companyUser
                ->setFirstName($userData->getFirstName())
                ->setPhone($phone)
                ->setEmail($userData->getEmail());

            $companyUser->save();
        }

        return $user;
    }

    public function editCompanyUser(CompanyUser $companyUser, CompanyUserData $userData): CompanyUser
    {
        $user = $companyUser->getUser();
        $phone = $this->phoneHelper->normalizePhone($userData->getPhone());

        $user
            ->setFirstName($userData->getFirstName())
            ->setPhone($phone);

        if ($userData->getPassword()) {
            $this->userService->changePassword($user, $userData->getPassword());
        }

        if ($user->isModified()) {
            $user->save();
        }

        $companyUser
            ->setFirstName($userData->getFirstName())
            ->setPhone($phone)
            ->setEmail($userData->getEmail())
            ->setComment($userData->getComment());

        if ($image = $userData->getImage()) {
            if ($image instanceof UploadedFile) {
                $file = $this->mediaService->uploadFile($image);
                $file = $this->mediaService->createFile($file);

                $companyUser->setImage(
                    ImageHelper::makeImage($file)
                );
            }

        } else {
            $companyUser->setImage(null);
        }

        $companyUser->save();

        $this->dispatcher->dispatch($event);
        $this->dispatcher->dispatch((new AfterEditCompanyUserEvent())->setCompanyUser($companyUser));

        return $companyUser;
    }

    public function changeActive(CompanyUser $companyUser): CompanyUser
    {
        $companyUser->setActive(!$companyUser->isActive())->save();

        return $companyUser;
    }

    public function deleteCompanyUser(CompanyUser $companyUser): void
    {
        $companyUser->delete();
    }

    public function getCompanyUsers(Company $company, ListConfiguration $configuration)
    {
        $query = CompanyUserQuery::create()->filterByCompany($company)->orderByCreatedAt(Criteria::DESC);

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function retrieveRuleByPk(int $pk): ?CompanyUserRule
    {
        return CompanyUserRuleQuery::create()->findPk($pk);
    }

    public function createUserRule(CompanyUser $companyUser, array $rules): CompanyUser
    {
        $rule = new CompanyUserRule();
        $rule
            ->setCompanyUser($companyUser)
            ->setRules($rules)
            ->save();

        return $companyUser;
    }

    public function editUserRule(CompanyUserRule $rule, array $rules): CompanyUser
    {
        $rule
            ->setRules($rules)
            ->save();

        return $rule->getCompanyUser();
    }

    public function deleteUserRule(CompanyUserRule $rule)
    {
        $rule->delete();
    }

    public function getCompanyUserRules(CompanyUser $user): ?CompanyUserRule
    {
        return CompanyUserRuleQuery::create()->filterByCompanyUser($user)->findOne();
    }

    public function checkAccess(User $user)
    {
        if ($user->isSubordinate()) {
            $companyUser = $user->getCompanyUser($user->getCompanyRelatedByActiveCompanyId());

            if (!$companyUser) {
                return true;
            }

            return CompanyUserRuleQuery::create()
                ->filterByCompanyUser($companyUser)
                ->filterByRules('%' . CompanyUserRule::RULE_CREATE_USER . '%', Criteria::LIKE)
                ->exists();
        }

        return true;
    }
}
