<?php


namespace App\Normalizer;


use App\Model\Company;
use App\Model\CompanyOrganizationShopQuery;
use App\Service\Buyer\BuyerService;
use App\Service\Company\CompanyFavoriteService;
use App\Service\Company\CompanyService;
use App\Service\Company\CompanyVerificationRequestService;
use App\Service\User\UserService;

class CompanyNormalizer extends AbstractNormalizer
{
    const GROUP_DETAIL = 'company.detail';
    const GROUP_DETAIL_CABINET = 'company.detail.cabinet';
    const GROUP_WITH_COMMENT = 'company.with.comment';
    const GROUP_WITH_JOB_REQUEST = 'company.with.job_request';
    const GROUP_WITH_SMART_SHOPS = 'company.with.smartShops';

    /**
     * @var CompanyVerificationRequestService
     */
    private CompanyVerificationRequestService $verificationRequestService;
    /**
     * @var CompanyFavoriteService
     */
    private CompanyFavoriteService $favoriteService;
    /**
     * @var UserService
     */
    private UserService $userService;
    /**
     * @var CompanyService
     */
    private CompanyService $companyService;
    /**
     * @var BuyerService
     */
    private BuyerService $buyerService;

    public function __construct(
        CompanyVerificationRequestService $verificationRequestService,
        CompanyFavoriteService $favoriteService,
        UserService $userService,
        CompanyService $companyService,
        BuyerService $buyerService
    ) {
        $this->verificationRequestService = $verificationRequestService;
        $this->favoriteService = $favoriteService;
        $this->userService = $userService;
        $this->companyService = $companyService;
        $this->buyerService = $buyerService;
    }

    /**
     * @var Company $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $user = $this->userService->getCurrentUser();
        $company = $user ? $user->getCompanyRelatedByActiveCompanyId() : null;

        $data = [
            'id' => $object->getId(),
            'inn' => $object->getInn(),
            'title' => $object->getTitle(),
            'email' => $object->getEmail(),
            'visible' => $object->isVisible(),
            'type' => $object->getTypeObject(),
            'image' => $object->getImage(),
        ];

        if ($this->hasGroup($context, self::GROUP_WITH_SMART_SHOPS)) {
            $data['shops'] = $object->getCompanyOrganizationShops(CompanyOrganizationShopQuery::create()->filterByApproveFromSmart(false));
        }

        $data['isFavorite'] = $company && $this->favoriteService->isFavorite($company, $object);

        if ($this->hasGroup($context, self::GROUP_WITH_COMMENT)) {
            $comment = $company ? $this->companyService->getCommentCompany($company, $object) : null;
            $data['comment'] = $comment ? $comment->getText() : '';
            $data['code'] = $comment ? $comment->getExternalCode() : '';
        }

        if ($this->hasGroup($context, self::GROUP_WITH_JOB_REQUEST)) {
            $data['isJobRequest'] = $company &&
                $object->isVerified() &&
                $this->buyerService->existBuyerJobRequest($company, $object);

            if ($company && $company->isSupplierCompany()) {
                $data['jobRequest'] = $this->buyerService->getBuyerJobRequest($object, $company);
            }
        }

        if ($this->hasGroup($context, self::GROUP_DETAIL)) {
            $data += [
                'diadocExternalCode' => $object->getDiadocExternalCode(),
                'docrobotExternalCode' => $object->getDocrobotExternalCode(),
                'storehouseExternalCode' => $object->getStorehouseExternalCode(),
                'description' => $object->getDescription(),
                'deliveryTerm' => $object->getDeliveryTerm(),
                'paymentTerm' => $object->getPaymentTerm(),
                'inn' => $object->getInn(),
                'kpp' => $object->getKpp(),
                'site' => $object->getSite(),
                'minOrderAmount' => $object->getMinOrderAmount(),
                'gallery' => $object->getGallery(),
            ];
        }

        if ($this->hasGroup($context, self::GROUP_DETAIL_CABINET)) {
            $data['canSendVerificationRequest'] = $this->verificationRequestService->canSendVerificationRequest($object);
            $data['lastVerificationRequest'] = $this->verificationRequestService->getLastVerificationRequest($object);
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Company;
    }
}
