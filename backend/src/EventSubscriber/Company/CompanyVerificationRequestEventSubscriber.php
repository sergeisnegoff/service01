<?php


namespace App\EventSubscriber\Company;


use App\Service\Company\CompanyService;
use App\Service\Company\Event\AfterChangeStatusVerificationRequestEvent;
use App\Service\Company\Event\AfterCreateCompanyVerificationRequestEvent;
use Creonit\MailingBundle\Mailing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class CompanyVerificationRequestEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var CompanyService
     */
    private CompanyService $companyService;
    /**
     * @var Mailing
     */
    private Mailing $mailing;
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    public function __construct(
        CompanyService $companyService,
        Mailing $mailing,
        MailerInterface $mailer
    ) {
        $this->companyService = $companyService;
        $this->mailing = $mailing;
        $this->mailer = $mailer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AfterCreateCompanyVerificationRequestEvent::class => 'afterCreateRequest',
            AfterChangeStatusVerificationRequestEvent::class => 'afterChangeStatusRequest',
        ];
    }

    public function afterCreateRequest(AfterCreateCompanyVerificationRequestEvent $event)
    {
        $verificationRequest = $event->getRequest();
        $company = $verificationRequest->getCompany();

        $this->companyService->changeCompanyStatus($company, $verificationRequest);
    }

    public function afterChangeStatusRequest(AfterChangeStatusVerificationRequestEvent $event)
    {
        $verificationRequest = $event->getRequest();
        $company = $verificationRequest->getCompany();

        $this->companyService->changeCompanyStatus($company, $verificationRequest);

        if ($email = $company->getEmail()) {
            $message = $this->mailing->buildMessage('verification_request_status', [
                'status' => $company->getVerificationStatusCaption(),
                'company' => $company->getTitle(),
            ]);

            $message->addTo($email);
            $this->mailer->send($message);
        }

        $this->companyService->sendStatusNotification($company, false);
    }
}
