<?php


namespace App\Service\Company\Event;


use App\Model\CompanyVerificationRequest;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCreateCompanyVerificationRequestEvent extends Event
{
    protected CompanyVerificationRequest $request;

    /**
     * @return CompanyVerificationRequest
     */
    public function getRequest(): CompanyVerificationRequest
    {
        return $this->request;
    }

    /**
     * @param CompanyVerificationRequest $request
     */
    public function setRequest(CompanyVerificationRequest $request): self
    {
        $this->request = $request;
        return $this;
    }
}
