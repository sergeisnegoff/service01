<?php


namespace App\Service\User;


use App\Model\Company;
use App\Model\User;
use App\Model\UserAccessToken;
use App\Model\UserAccessTokenQuery;
use App\Service\User\Exception\ReachedMaximumAttemptsGenerateUniqueToken;

class UserAccessTokenService
{
    const GENERATE_UNIQUE_TOKEN_ATTEMPTS = 100;
    const TOKEN_LIFETIME = 2592000; // 30 days

    public function createAccessToken(User $user, Company $company = null): UserAccessToken
    {
        $accessToken = new UserAccessToken();
        $accessToken->setUser($user);
        $accessToken->setCompany($company);
        $accessToken->setToken($this->generateUniqueToken());
        $accessToken->setExpiredAt(new \DateTime(sprintf('+%d seconds', static::TOKEN_LIFETIME)));
        $accessToken->save();

        return $accessToken;
    }

    public function findAccessToken(string $token): ?UserAccessToken
    {
        return UserAccessTokenQuery::create()->findOneByToken($token);
    }

    public function findUserAccessToken(User $user): ?UserAccessToken
    {
        return UserAccessTokenQuery::create()->findOneByUserId($user->getId());
    }

    public function findCompanyAccessToken(Company $company): ?UserAccessToken
    {
        return UserAccessTokenQuery::create()->findOneByCompanyId($company->getId());
    }

    public function isAccessTokenExpired(UserAccessToken $accessToken): bool
    {
        if (!$accessToken->getExpiredAt()) {
            return false;
        }

        return $accessToken->getExpiredAt() < new \DateTime('now');
    }

    public function generateUniqueToken(): string
    {
        $attempts = static::GENERATE_UNIQUE_TOKEN_ATTEMPTS;

        while ($token = $this->generateRandomToken() and $attempts-- > 0) {
            if (!$this->findAccessToken($token)) {
                return $token;
            }
        }

        throw new ReachedMaximumAttemptsGenerateUniqueToken();
    }

    public function generateRandomToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
