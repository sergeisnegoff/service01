<?php


namespace App\EventSubscriber\Buyer;


use App\Service\Buyer\Event\AfterCreateJobRequestEvent;
use Creonit\MailingBundle\Mailing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class BuyerEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailing
     */
    private Mailing $mailing;
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    public function __construct(
        Mailing $mailing,
        MailerInterface $mailer
    )
    {
        $this->mailing = $mailing;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterCreateJobRequestEvent::class => 'onAfterCreateJobRequest',
        ];
    }

    public function onAfterCreateJobRequest(AfterCreateJobRequestEvent $event)
    {
        $jobRequest = $event->getJobRequest();
        $email = $jobRequest->getCompanyRelatedBySupplierId()->getEmail();

        if (!$email) {
            return;
        }

        $message = $this->mailing->buildMessage('new_job_request', [
            'message' => $jobRequest->getText(),
        ]);

        $message->addTo($email);
        $this->mailer->send($message);
    }
}
