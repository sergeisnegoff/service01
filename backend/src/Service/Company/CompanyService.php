<?php


namespace App\Service\Company;


use App\EventPublisher\EventPublisher;
use App\Helper\ImageHelper;
use App\Model\Company;
use App\Model\CompanyComment;
use App\Model\CompanyCommentQuery;
use App\Model\CompanyQuery;
use App\Model\CompanyUser;
use App\Model\CompanyUserQuery;
use App\Model\CompanyVerificationRequest;
use App\Model\Notification;
use App\Model\User;
use App\Service\Company\CompanyData\UpdateCompanyData;
use App\Service\Company\CompanyList\CompanyListContext;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Notification\NotificationService;
use App\Service\Notification\NotificationUserData;
use Creonit\MediaBundle\Model\GalleryItemQuery;
use Creonit\MediaBundle\Service\MediaService;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class CompanyService
{
    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;
    /**
     * @var MediaService
     */
    private MediaService $mediaService;
    private $kernelProjectDir;
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;
    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;
    private DataObjectBuilder $dataObjectBuilder;

    public function __construct(
        ListConfigurationService $listConfigurationService,
        MediaService $mediaService,
        $kernelProjectDir,
        NotificationService $notificationService,
        UrlGeneratorInterface $urlGenerator,
        EventPublisher $eventPublisher,
        DataObjectBuilder $dataObjectBuilder
    ) {
        $this->listConfigurationService = $listConfigurationService;
        $this->mediaService = $mediaService;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->notificationService = $notificationService;
        $this->urlGenerator = $urlGenerator;
        $this->eventPublisher = $eventPublisher;
        $this->dataObjectBuilder = $dataObjectBuilder;
    }

    public function retrieveByInn($inn): ?Company
    {
        return CompanyQuery::create()
            ->filterByInn($inn)
            ->where('(
                (company.from_smart = 1 AND company.approve_from_smart = 1) OR
                company.from_smart = 0
            )')
            ->findOne();
    }

    public function retrieveById($id): ?Company
    {
        return CompanyQuery::create()->findPk($id);
    }

    public function retrieveByCode($code): ?Company
    {
        return CompanyQuery::create()->findOneByExternalCode($code);
    }

    public function retrieveByIdOrCode($id): ?Company
    {
        if (is_numeric($id)) {
            return $this->retrieveById($id) ?? $this->retrieveByCode($id);
        }

        return $this->retrieveByCode($id);
    }

    public function retrieveByCommentExternalCode($code)
    {
        return CompanyQuery::create()
            ->useCompanyCommentRelatedByCommentIdQuery()
                ->filterByExternalCode($code)
            ->endUse()
            ->findOne();
    }

    public function createFirstCompany(User $user, int $type): Company
    {
        $company = new Company();
        $company
            ->setUserRelatedByUserId($user)
            ->setTitle('Моя организация')
            ->setType($type)
            ->save();

        return $company;
    }

    public function getCompanies(CompanyListContext $context, ListConfiguration $configuration)
    {
        $query = CompanyQuery::create();
        $user = $context->getUser();

        if ($user->isSubordinate()) {
            $query
                ->useCompanyUserQuery(null, Criteria::LEFT_JOIN)
                ->endUse()
                ->where("((company_user.active = 1 OR company_user.id IS NULL) AND (company_user.user_id = {$user->getId()} OR company.user_id = {$user->getId()}))")
                ->distinct();

        } else {
            $query->filterByUserRelatedByUserId($user);
        }

        if ($context->isSmart()) {
            $query
                ->useCompanyOrganizationShopQuery()
                    ->filterByApproveFromSmart(false)
                ->endUse()
                ->filterByFromSmart(true);

            $user
                ->setSmartShown(true)
                ->save();

        } else {
            $query->where('(
                company.from_smart = 1 and company.approve_from_smart = 1 OR
                company.from_smart = 0
            )');
        }

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function editCompanyTitle(Company $company, string $title): Company
    {
        $company->setTitle($title);
        $company->save();

        return $company;
    }

    public function createCompany(User $user, string $title, int $type, string $inn): Company
    {
        $company = new Company();
        $company
            ->setUserRelatedByUserId($user)
            ->setTitle($title)
            ->setType($type)
            ->setInn($inn)
            ->save();

        return $company;
    }

    public function deleteCompany(Company $company): void
    {
        $company->delete();
    }

    public function changeVisible(Company $company): void
    {
        $company->setVisible(!$company->isVisible())->save();
    }

    public function chooseCompany(User $user, Company $company): User
    {
        $user->setCompanyRelatedByActiveCompanyId($company)->save();
        return $user;
    }

    public function fillCompany(Company $company, UpdateCompanyData $companyData): Company
    {
        $company
            ->setDiadocExternalCode($companyData->getDiadocExternalCode())
            ->setDocrobotExternalCode($companyData->getDocrobotExternalCode())
            ->setStorehouseExternalCode($companyData->getStorehouseExternalCode())
            ->setTitle($companyData->getTitle())
            ->setDescription($companyData->getDescription())
            ->setInn($companyData->getInn())
            ->setKpp($companyData->getKpp())
            ->setSite($companyData->getSite())
            ->setDeliveryTerm($companyData->getDeliveryTerm())
            ->setPaymentTerm($companyData->getPaymentTerm())
            ->setEmail($companyData->getEmail())
            ->setMinOrderAmount($companyData->getMinOrderAmount())
        ;

        if ($logo = $companyData->getLogo()) {
            if ($logo instanceof UploadedFile) {
                $file = $this->mediaService->uploadFile($logo);
                $file = $this->mediaService->createFile($file);

                $company->setImage(
                    ImageHelper::makeImage($file)
                );
            }

        } else {
            $company->setImage(null);
        }

        if ($images = $companyData->getImages()) {
            $this->fillCompanyGallery($company, $images);
        }

        if ($deleteImagesId = $companyData->getDeleteImagesId()) {
            $this->clearGallery($deleteImagesId);
        }

        if ($company->isModified()) {
            $company->save();
        }

        return $company;
    }

    protected function fillCompanyGallery(Company $company, array $images)
    {
        $gallery = $company->getGalleryId() ? $company->getGallery() : ImageHelper::makeGallery();
        $isNew = $gallery->isNew();

        foreach ($images as $image) {
            $file = $this->mediaService->createFile($this->mediaService->uploadFile($image));
            $image = ImageHelper::makeImage($file);

            $galleryItem = ImageHelper::makeGalleryItem();
            $galleryItem->setImage($image);

            $gallery->addGalleryItem($galleryItem);
        }

        $gallery->save();

        if ($isNew) {
            $company->setGallery($gallery)->save();
        }
    }

    protected function clearGallery(array $filesId)
    {
        $galleryItems = GalleryItemQuery::create()
            ->useImageQuery()
                ->filterByFileId($filesId)
            ->endUse()
            ->distinct()
            ->find();

        if (!$galleryItems->count()) {
            return;
        }

        GalleryItemQuery::create()->filterById($galleryItems->getColumnValues('Id'))->delete();
    }

    public function changeCompanyStatus(Company $company, CompanyVerificationRequest $verificationRequest): Company
    {
        $company->setVerificationStatus($verificationRequest->getStatus())->save();

        return $company;
    }

    public function getCommentCompanyText(Company $company, Company $commentCompany): string
    {
        $comment = $this->getCommentCompany($company, $commentCompany);

        return $comment ? $comment->getText() : '';
    }

    public function getCommentCompany(Company $company, Company $commentCompany): ?CompanyComment
    {
        return CompanyCommentQuery::create()
            ->filterByCompanyRelatedByCompanyId($company)
            ->filterByCompanyRelatedByCommentId($commentCompany)
            ->findOne();
    }

    public function commentCompany(
        Company $company,
        Company $commentCompany,
        string $text = '',
        string $externalCode = '',
        string $alternativeTitle = ''
    ): Company {
        $comment = $this->getCommentCompany($company, $commentCompany) ?: new CompanyComment();

        if ($comment->isNew()) {
            $comment
                ->setCompanyRelatedByCompanyId($company)
                ->setCompanyRelatedByCommentId($commentCompany);
        }

        $comment
            ->setText($text)
            ->setExternalCode($externalCode)
            ->setAlternativeTitle($alternativeTitle)
            ->save();

        return $commentCompany;
    }

    public function sendStatusNotification(Company $company, bool $duplicateByEmail = true)
    {
        $verificationStatus = $company->getVerificationStatus();

        if (!in_array($verificationStatus, [CompanyVerificationRequest::STATUS_VERIFIED, CompanyVerificationRequest::STATUS_BLACK_LIST])) {
            return;
        }

        $notificationCode = $verificationStatus == CompanyVerificationRequest::STATUS_VERIFIED ?
            Notification::CODE_MODERATION_PASSED :
            Notification::CODE_MODERATION_FAILED;

        if (!$notification = $this->notificationService->retrieveByCode($notificationCode)) {
            return;
        }

        $users = $company->getCompanyUsersData();
        $link = $this->urlGenerator->generate('company', [], Router::ABSOLUTE_URL);

        foreach ($users as $user) {
            $data = $this->dataObjectBuilder->build(NotificationUserData::class, [
                'user' => $user,
                'notification' => $notification,
                'link' => $link,
                'supplier' => $company,
            ]);

            $userNotification = $this->notificationService->createUserNotification($data);

            $this->eventPublisher->publish(
                $user,
                $userNotification,
                [
                    EventPublisher::MESSAGE_TYPE => NotificationService::EVENT_MESSAGE_TYPE_NEW_NOTIFICATION
                ]
            );
        }

        if (isset($userNotification) && $duplicateByEmail) {
            $this->notificationService->doDuplicateByEmail($company, $userNotification);
        }
    }

    public function changeCompanyOwner(Company $company, User $newOwner): void
    {
        $owner = $company->getUserRelatedByUserId();

        if ($owner->countCompaniesRelatedByUserId() === 1) {
            $firstCompany = $this->createFirstCompany($owner, $company->getType());
            $this->chooseCompany($owner, $firstCompany);

        } else if ($company->getId() === $owner->getActiveCompanyId()) {
            $this->chooseCompany($owner, $this->getRandomCompany($owner, [$company->getId()]));
        }

        $company->setUserRelatedByUserId($newOwner)->save();

        CompanyUserQuery::create()
            ->filterByCompany($company)
            ->filterByUser($owner)
            ->delete();
    }

    public function getRandomCompany(User $user, array $excludeIds = []): ?Company
    {
        $query = CompanyQuery::create()
            ->filterByUserRelatedByUserId($user)
            ->addAscendingOrderByColumn('RAND()');

        if ($excludeIds) {
            $query->filterById($excludeIds, Criteria::NOT_IN);
        }

        return $query->findOne();
    }

    public function sendJoinCompaniesNotification(User $user, array $inns)
    {
        foreach ($inns as $inn) {
            $company = $this->retrieveByInn($inn);

            if (!$company) {
                continue;
            }

            $this->sendJoinCompanyNotification($user, $company);
        }
    }

    public function sendJoinCompanyNotification(User $user, Company $company)
    {
        if (!$notification = $this->notificationService->retrieveByCode(Notification::CODE_JOIN_COMPANY)) {
            return;
        }

        $users = $company->getCompanyUsersData();

        $text = strtr($notification->getText(), [
            '#company#' => $company->getTitle(),
            '#phone#' => $user->getPhone(),
            '#name#' => $user->getFirstName(),
        ]);

        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }
            $data = $this->dataObjectBuilder->build(NotificationUserData::class, [
                'user' => $user,
                'notification' => $notification,
                'link' => '',
                'supplier' => $company,
                'text' => $text,
            ]);

            $userNotification = $this->notificationService->createUserNotification($data);

            $this->eventPublisher->publish(
                $user,
                $userNotification,
                [
                    EventPublisher::MESSAGE_TYPE => NotificationService::EVENT_MESSAGE_TYPE_NEW_NOTIFICATION
                ]
            );
        }

        if (isset($userNotification)) {
            $this->notificationService->doDuplicateByEmail($company, $userNotification);
        }
    }

    public function getCompaniesForSmartImport(User $user)
    {
        return CompanyQuery::create()
            ->filterByUserRelatedByUserId($user)
            ->filterByFromSmart(true)
            ->find()
            ->toKeyIndex('Inn');
    }
}
