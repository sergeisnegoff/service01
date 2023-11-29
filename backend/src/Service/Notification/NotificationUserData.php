<?php
declare(strict_types=1);

namespace App\Service\Notification;

use App\Model\Company;
use App\Model\CompanyOrganizationShop;
use App\Model\Invoice;
use App\Model\Notification;
use App\Model\User;
use App\Service\DataObject\DataObjectInterface;

class NotificationUserData implements DataObjectInterface
{
    public User $user;
    public Notification $notification;
    public string $link = '';
    public string $text = '';
    public ?Company $supplier = null;
    public ?Company $buyer = null;
    public ?Invoice $invoice = null;
    public ?CompanyOrganizationShop $shop = null;

    /**
     * @param User $user
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param Notification $notification
     */
    public function setNotification(Notification $notification): self
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param Company|null $supplier
     */
    public function setSupplier(?Company $supplier): self
    {
        $this->supplier = $supplier;
        return $this;
    }

    /**
     * @param Company|null $buyer
     */
    public function setBuyer(?Company $buyer): self
    {
        $this->buyer = $buyer;
        return $this;
    }

    /**
     * @param Invoice|null $invoice
     */
    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @param CompanyOrganizationShop|null $shop
     */
    public function setShop(?CompanyOrganizationShop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }

}
