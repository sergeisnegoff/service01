<?php


namespace App\EventSubscriber\Company;


use App\Helper\PhoneHelper;
use App\Service\Company\Event\InviteCompanyUserEvent;
use App\Service\Megafon\MegafonService;
use Creonit\MailingBundle\Mailing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CompanyEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailing
     */
    private Mailing $mailing;
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;
    private MegafonService $megafonService;
    private PhoneHelper $phoneHelper;

    public function __construct(
        Mailing $mailing,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        MegafonService $megafonService,
        PhoneHelper $phoneHelper
    ) {
        $this->mailing = $mailing;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->megafonService = $megafonService;
        $this->phoneHelper = $phoneHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            InviteCompanyUserEvent::class => 'onInviteCompanyUser',
        ];
    }

    public function onInviteCompanyUser(InviteCompanyUserEvent $event)
    {
        $userData = $event->getUserData();
        $companyUser = $event->getCompanyUser();

        if ($userData->getPhone()) {
            $this->megafonService->send(
                $this->phoneHelper->normalizePhone($userData->getPhone()),
                sprintf(
                    'Вас пригласили для прохождения регистрации. %s',
                    $this->urlGenerator->generate('register', ['invite' => $companyUser->getHash()], UrlGeneratorInterface::ABSOLUTE_URL)
                )
            );
        }

        if ($userData->getEmail()) {
            $message = $this->mailing->buildMessage('invite_company_user', [
                'link' => $this->urlGenerator->generate('register', ['invite' => $companyUser->getHash()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            $message->addTo($userData->getEmail());
            $this->mailer->send($message);
        }
    }
}
