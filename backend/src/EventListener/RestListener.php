<?php


namespace App\EventListener;


use Creonit\RestBundle\Exception\RestErrorException;
use Creonit\RestBundle\Handler\RestError;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class RestListener
{
    /** @var  ContainerInterface */
    protected $container;
    /** @var LoggerInterface  */
    protected $logger;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(ContainerInterface $container, LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!preg_match('#^/api/#', $event->getRequest()->getPathInfo())) {
            return;
        }

        if ($this->container->get('request_stack')->getCurrentRequest() !== $event->getRequest()) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof RestErrorException) {
            $error = $exception->getRestError();
            $message = $error->getMessage();

            if ($message === 'Генерация недоступна') {
                $time = $error->request->get('time');
                $error->setMessage(sprintf('СМС-код можно повторно запросить через %d сек', $time));
            }

        } else if ($exception instanceof NotFoundHttpException) {
            $error = new RestError();
            $error->setMessage('Метод не найден');
            $error->setCode(404);
            $error->setStatus(404);

        } else {
            $error = new RestError();
            $error->setMessage(
                $this->container->getParameter('kernel.debug')
                    ? $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine() . ' | ' . $exception->getTraceAsString()
                    : 'Системная ошибка'
            );
            $error->setCode($exception->getCode());
            $error->setStatus(500);

            $this->logger->critical($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine() . ' | ' . $exception->getTraceAsString());
        }

        $event->setResponse(new JsonResponse($error->dump(), $error->getStatus()));
    }
}
