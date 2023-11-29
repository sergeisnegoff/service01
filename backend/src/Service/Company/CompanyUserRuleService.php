<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\Model\CompanyUser;
use App\Model\CompanyUserRule;
use App\Model\CompanyUserRuleQuery;
use App\Model\User;
use App\Service\User\UserService;
use Creonit\RestBundle\Handler\RestHandler;

class CompanyUserRuleService
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function isGranted(RestHandler $handler, string $checkRule): void
    {
        /** @var User $user */
        $user = $this->userService->getCurrentUser();

        if (!$user) {
            $this->forbidden($handler);
        }

        $company = $user->getCompanyRelatedByActiveCompanyId();

        if (!$company) {
            $this->forbidden($handler);
        }

        if (!$user->isSubordinate()) {
            return;
        }

        if (!$companyUser = $user->getCompanyUser($company)) {
            return;
        }

        $rules = $this->getRules($companyUser);

        foreach ($rules as $rule) {
            if (in_array($checkRule, $rule->getRules())) {
                return;
            }
        }

        $this->forbidden($handler);
    }

    protected function getRules(CompanyUser $user)
    {
        return CompanyUserRuleQuery::create()->filterByCompanyUser($user)->find();
    }

    protected function forbidden(RestHandler $handler): void
    {
        $handler->error->forbidden('Доступ запрещен');
    }
}
