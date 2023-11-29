<?php
declare(strict_types=1);

namespace App\Service\ApiRequestLog;

use App\Model\ApiRequestLog;
use App\Model\User;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestLogService
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function processLog(Request $request, Response $response): ?ApiRequestLog
    {
        if (!$this->checkRequest($request)) {
            return null;
        }

        return $this->createLog($request, $response);
    }

    protected function createLog(Request $request, Response $response): ApiRequestLog
    {
        /** @var User|null $user */
        $user = $this->userService->getCurrentUser();
        $company = $user ? $user->getCompanyRelatedByActiveCompanyId() : null;

        $log = new ApiRequestLog();
        $log
            ->setCompany($company)
            ->setToken($request->headers->get('Token'))
            ->setStatusCode($response->getStatusCode())
            ->setMethod($request->getMethod())
            ->setUri($request->getRequestUri())
            ->setRequestData($this->getRequestData($request))
            ->setResponseData($response->getContent())
            ->save();

        return $log;
    }

    /**
     * @return false|string
     */
    protected function getRequestData(Request $request)
    {
        $bag = $request->getMethod() === ApiRequestLog::METHOD_GET ? 'query' : 'request';
        return json_encode($request->$bag->all());
    }

    protected function checkRequest(Request $request): bool
    {
        return preg_match('/^\/api\//', $request->getRequestUri(), $matches) &&
            in_array($request->getMethod(), ApiRequestLog::$methods);
    }
}
