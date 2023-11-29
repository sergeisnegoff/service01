<?php


namespace App\Service\Notification;


use App\Model\Company;
use App\Model\Notification;
use App\Model\NotificationQuery;
use App\Model\User;
use App\Model\UserNotification;
use App\Model\UserNotificationQuery;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Notification\NotificationList\NotificationListContext;
use Creonit\MailingBundle\Mailing;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NotificationService
{
    const EVENT_MESSAGE_TYPE_NEW_NOTIFICATION = 'newNotification';
    const EMAIL_TEMPLATE = 'new_notification';

    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;
    private Mailing $mailing;
    private MailerInterface $mailer;

    public function __construct(
        ListConfigurationService $listConfigurationService,
        Mailing $mailing,
        MailerInterface $mailer
    ) {
        $this->listConfigurationService = $listConfigurationService;
        $this->mailing = $mailing;
        $this->mailer = $mailer;
    }

    public function retrieveByCode(string $code): ?Notification
    {
        return NotificationQuery::create()->findOneByCode($code);
    }

    public function createUserNotification(NotificationUserData $data): UserNotification
    {
        $userNotification = new UserNotification();
        $userNotification
            ->setUser($data->user)
            ->setNotification($data->notification)
            ->setLink($data->link)
            ->setText($data->text)
            ->setCompanyRelatedByBuyerId($data->buyer)
            ->setCompanyRelatedBySupplierId($data->supplier)
            ->setCompanyOrganizationShop($data->shop)
            ->setInvoice($data->invoice)
            ->save();

        return $userNotification;
    }

    public function countUnreadNotifications(User $user): int
    {
        $query = UserNotificationQuery::create()
            ->filterByUser($user)
            ->filterByReaded(false);

        return $query->count();
    }

    public function getNotificationList(NotificationListContext $context, ListConfiguration $configuration)
    {
        $query = UserNotificationQuery::create()
            ->filterByUser($context->getUser())
            ->orderByCreatedAt(Criteria::DESC);

        if ($dateFrom = $context->getDateFrom()) {
            $query->filterByCreatedAt($dateFrom, Criteria::GREATER_EQUAL);
        }

        if ($dateTo = $context->getDateTo()) {
            $query->filterByCreatedAt($dateTo, Criteria::LESS_EQUAL);
        }

        if ($context->getRead() !== null) {
            $query->filterByReaded(filter_var($context->getRead(), FILTER_VALIDATE_BOOL));
        }

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function retrieveByPk($pk): ?UserNotification
    {
        return UserNotificationQuery::create()->findOneById($pk);
    }

    public function readUserNotification(UserNotification $notification): UserNotification
    {
        $notification->setReaded(true);
        $notification->save();
        return $notification;
    }

    protected array $duplicateMessages = [];

    public function doDuplicateByEmail(
        Company $company,
        UserNotification $userNotification,
        string $template = self::EMAIL_TEMPLATE
    ) {
        if (!$company->getEmail()) {
            return null;
        }

        $notification = $userNotification->getNotification();

        $message = $this->mailing->buildMessage($template, [
            'text' => $userNotification->getText() ?: $notification->getText(),
            'link' => $userNotification->getLink(),
        ]);

        $message->addTo($company->getEmail());

        $this->duplicateMessages[] = $message;
    }

    public function onTerminate()
    {
        while ($message = array_shift($this->duplicateMessages)) {
            $this->mailer->send($message);
        }
    }
}
