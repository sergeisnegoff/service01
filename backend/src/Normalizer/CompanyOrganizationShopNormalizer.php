<?php


namespace App\Normalizer;


use App\Model\Company;
use App\Model\CompanyOrganizationShop;
use App\Service\Buyer\BuyerOrganizationService;
use App\Service\User\UserService;

class CompanyOrganizationShopNormalizer extends AbstractNormalizer
{
    const GROUP_MASS_ADDITION = 'shop.massAddition';
    const GROUP_DETAIL = 'shop.detail';

    /**
     * @var BuyerOrganizationService
     */
    private BuyerOrganizationService $buyerOrganizationService;
    /**
     * @var UserService
     */
    private UserService $userService;

    public function __construct(
        BuyerOrganizationService $buyerOrganizationService,
        UserService $userService
    ) {
        $this->buyerOrganizationService = $buyerOrganizationService;
        $this->userService = $userService;
    }

    /**
     * @var CompanyOrganizationShop $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->hasGroup($context, self::GROUP_MASS_ADDITION)) {
            $companyCode = $this->buyerOrganizationService->getCompanyShopCode($context['company'], $object);

            return [
                'id' => $object->getId(),
                'cod' => $companyCode ? $companyCode->getExternalCode() : '',
            ];
        }

        $user = $this->userService->getCurrentUser();
        $company = $user ? $user->getCompanyRelatedByActiveCompanyId() : null;
        $companyCode = null;

        // TODO: Заполняем company_shop_code от лица поставщика, а код получаем по той компании, которой принадлежат точки
        // добавил передачу компании пользователя, который запрашивает точки.
        if (isset($context['myCompany']) && $context['myCompany'] instanceof Company) {
            $companyCode = $this->buyerOrganizationService->getCompanyShopCode($context['myCompany'], $object);
        }

        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'address' => $object->getAddress(),
            'latitude' => $object->getLatitude(),
            'longitude' => $object->getLongitude(),
            'code' => $companyCode ? $companyCode->getExternalCode() : '',
        ];

        if ($companyCode) {
            $data['alternativeTitle'] = $companyCode->getAlternativeTitle();

        } else {
            $data['alternativeTitle'] = $company ? $this->buyerOrganizationService->getAlternativeTitleShopText($company, $object) : '';
        }

        if ($this->hasGroup($context, self::GROUP_DETAIL)) {
            $data += [
                'diadocExternalCode' => $object->getDiadocExternalCode(),
                'docrobotExternalCode' => $object->getDocrobotExternalCode(),
            ];
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof CompanyOrganizationShop;
    }
}
