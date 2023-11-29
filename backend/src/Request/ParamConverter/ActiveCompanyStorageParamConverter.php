<?php


namespace App\Request\ParamConverter;


use App\Model\CompanyQuery;
use App\Model\User;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\User\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ActiveCompanyStorageParamConverter implements ParamConverterInterface
{
    /**
     * @var UserService
     */
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        /** @var User|null $user */
        $user = $this->userService->getCurrentUser();
        $storage = new ActiveCompanyStorage();

        if ($user) {
            $storage->setCompany($user->getCompanyRelatedByActiveCompanyId() ?: CompanyQuery::create()->filterByUserRelatedByUserId($user)->findOne());
        }

        $request->attributes->set($configuration->getName(), $storage);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === ActiveCompanyStorage::class;
    }
}
