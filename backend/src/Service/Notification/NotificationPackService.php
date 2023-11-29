<?php
declare(strict_types=1);

namespace App\Service\Notification;

use App\Model\NotificationPack;
use App\Model\NotificationPackQuery;
use App\Model\User;
use App\Model\UserNotificationQuery;
use App\Service\Notification\NotificationList\NotificationListContext;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;

class NotificationPackService
{
    protected function retrievePack(User $user, int $pack): ?NotificationPack
    {
        return NotificationPackQuery::create()
            ->filterByUser($user)
            ->filterByNumber($pack)
            ->findOne();
    }

    protected function getExcludedId(User $user): array
    {
        $collection = NotificationPackQuery::create()
            ->filterByUser($user)
            ->find();

        $data = [];

        foreach ($collection as $pack) {
            $data = array_merge($data, $pack->getUserNotificationId());
        }

        return $data;
    }

    protected function createPack(User $user, int $pack, array $notificationsId): NotificationPack
    {
        $notificationPack = new NotificationPack();
        $notificationPack
            ->setUser($user)
            ->setNumber($pack)
            ->setUserNotificationId($notificationsId)
            ->save();

        return $notificationPack;
    }

    public function getNotificationList(NotificationListContext $context): ObjectCollection
    {
        $user = $context->getUser();
        $numberPack = $context->getPack();

        $query = UserNotificationQuery::create()->filterByUser($user);

        if ($pack = $this->retrievePack($user, $numberPack)) {
            return $query->filterById($pack->getUserNotificationId())->find();
        }

        $response = $query->filterById($this->getExcludedId($user), Criteria::NOT_IN)->find();

        if ($notificationsId = $response->getColumnValues('Id')) {
            $this->createPack($user, $numberPack, $notificationsId);
        }

        return $response;
    }
}
