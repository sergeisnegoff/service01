<?php
declare(strict_types=1);

namespace App\Service\Notification\NotificationList;

use App\Model\User;

class NotificationListContext
{
    protected ?\DateTime $dateFrom = null;
    protected ?\DateTime $dateTo = null;
    protected User $user;
    protected ?int $pack = null;
    protected ?bool $read = null;

    /**
     * @return bool|null
     */
    public function getRead(): ?bool
    {
        return $this->read;
    }

    /**
     * @param bool|null $read
     */
    public function setRead(?bool $read): self
    {
        $this->read = $read;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPack(): ?int
    {
        return $this->pack;
    }

    /**
     * @param int|null $pack
     */
    public function setPack(?int $pack): self
    {
        $this->pack = $pack;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @param string $dateFrom
     */
    public function setDateFrom(string $dateFrom): self
    {
        if (strtotime($dateFrom)) {
            $dateFrom = new \DateTime($dateFrom);

        } else {
            $dateFrom = null;
        }

        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    /**
     * @param string $dateTo
     */
    public function setDateTo(string $dateTo): self
    {
        if (strtotime($dateTo)) {
            $dateTo = new \DateTime($dateTo);

        } else {
            $dateTo = null;
        }

        $this->dateTo = $dateTo;
        return $this;
    }
}
