<?php


namespace App\EventSubscriber;


use App\Helper\PhoneHelper;
use App\Service\ApiRequestLog\ApiRequestLogService;
use App\Service\Company\CompanyService;
use App\Service\Megafon\MegafonService;
use App\Service\Notification\NotificationService;
use App\Service\User\UserAccessTokenService;
use App\Service\User\UserService;
use Creonit\VerificationCodeBundle\Event\CreateCodeEvent;
use Creonit\VerificationCodeBundle\Event\VerificationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EventSubscriber implements EventSubscriberInterface
{
    private ApiRequestLogService $requestLogService;
    private NotificationService $notificationService;
    private MegafonService $megafonService;
    private PhoneHelper $phoneHelper;
    private UserAccessTokenService $userAccessTokenService;
    private UserService $userService;
    private CompanyService $companyService;

    public function __construct(
        ApiRequestLogService $requestLogService,
        NotificationService $notificationService,
        MegafonService $megafonService,
        PhoneHelper $phoneHelper,
        UserAccessTokenService $userAccessTokenService,
        UserService $userService,
        CompanyService $companyService
    ) {
        $this->requestLogService = $requestLogService;
        $this->notificationService = $notificationService;
        $this->megafonService = $megafonService;
        $this->phoneHelper = $phoneHelper;
        $this->userAccessTokenService = $userAccessTokenService;
        $this->userService = $userService;
        $this->companyService = $companyService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::TERMINATE => 'onKernelTerminate',
            VerificationEvents::CREATE_CODE => 'onCreateVerificationCode',
        ];
    }

    public function onCreateVerificationCode(CreateCodeEvent $event)
    {
        $code = $event->getVerificationCode();

//        if ($code->getScope() === 'phone') {
//            $this->megafonService->send(
//                $this->phoneHelper->normalizePhone($code->getKey()),
//                sprintf('Код подтверждения: %s', $code->getCode())
//            );
//        }
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $user = $this->userService->getCurrentUser();

        if ($user && $token = $request->headers->get('Token')) {
            $token = $this->userAccessTokenService->findAccessToken($token);

            if ($token && $token->getCompanyId() && $token->getCompanyId() !== $user->getActiveCompanyId()) {
                $this->companyService->chooseCompany($user, $token->getCompany());
            }
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        $this->requestLogService->processLog($request, $response);
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        $this->notificationService->onTerminate();
    }
}
