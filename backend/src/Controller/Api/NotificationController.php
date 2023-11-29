<?php


namespace App\Controller\Api;


use App\Service\ListConfiguration\ListConfiguration;
use App\Service\Notification\NotificationList\NotificationListContext;
use App\Service\Notification\NotificationPackService;
use App\Service\Notification\NotificationService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notifications")
 */
class NotificationController extends AbstractController
{
    /**
     * Список уведомлений
     *
     * @QueryParameter("dateFrom", type="string", description="Дата(от)")
     * @QueryParameter("dateTo", type="string", description="Дата(до)")
     *
     * @QueryParameter("read", type="boolean", description="Получить все или только не прочитанные")
     *
     * @QueryParameter("pack", type="integer", description="Номер pack")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("", methods={"GET"})
     */
    public function getNotifications(
        RestHandler $handler,
        NotificationService $notificationService,
        ListConfiguration $listConfiguration,
        NotificationPackService $packService
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();
        $pack = $request->query->getInt('pack');

        $context = new NotificationListContext();
        $context
            ->setUser($user)
            ->setDateFrom($request->query->get('dateFrom', ''))
            ->setDateTo($request->query->get('dateTo', ''))
            ->setRead($request->query->has('read') ? $request->query->getBoolean('read') : null)
            ->setPack($pack);

        if ($pack) {
            $handler->data->set($packService->getNotificationList($context));

        } else {
            $handler->data->set($notificationService->getNotificationList($context, $listConfiguration));
        }

        return $handler->response();
    }

    /**
     * Количество непрочитанных уведомлений
     *
     * @Route("/unread", methods={"GET"})
     */
    public function getUnreadNotifications(
        RestHandler $handler,
        NotificationService $notificationService
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();

        return $handler->response([
            'count' => $notificationService->countUnreadNotifications($user)
        ]);
    }

    /**
     * Записать просмотр уведомления
     *
     * @RequestParameter("lastNotificationId", type="integer", description="ID уведомления")
     *
     * @Route("/read", methods={"PUT"})
     */
    public function readNotifications(
        RestHandler $handler,
        NotificationService $notificationService
    ) {
        $handler->checkAuthorization();
        $handler->validate([
            'request' => [
                'lastNotificationId' => [new NotBlank()],
            ]
        ]);

        $request = $handler->getRequest();

        if (!$notification = $notificationService->retrieveByPk($request->request->get('lastNotificationId'))) {
            $handler->error->notFound();
        }

        $notificationService->readUserNotification($notification);

        return $handler->response(['success' => true]);
    }
}
