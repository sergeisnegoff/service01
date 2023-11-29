<?php


namespace App\Service\Buyer\Event;


use App\Model\BuyerJobRequest;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCreateJobRequestEvent extends Event
{
    protected BuyerJobRequest $jobRequest;

    /**
     * @return BuyerJobRequest
     */
    public function getJobRequest(): BuyerJobRequest
    {
        return $this->jobRequest;
    }

    /**
     * @param BuyerJobRequest $jobRequest
     */
    public function setJobRequest(BuyerJobRequest $jobRequest): self
    {
        $this->jobRequest = $jobRequest;
        return $this;
    }
}
