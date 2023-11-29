<?php

namespace App\Model;

use App\Model\Base\Company as BaseCompany;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Skeleton subclass for representing a row from the 'company' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Company extends BaseCompany
{
    const
        TYPE_SUPPLIER = 1,
        TYPE_BUYER = 2;

    public static array $typeCaptions = [
        self::TYPE_SUPPLIER => 'Поставщик',
        self::TYPE_BUYER => 'Покупатель',
    ];

    public static array $typeCodes = [
        self::TYPE_SUPPLIER => 'supplier',
        self::TYPE_BUYER => 'buyer',
    ];

    public static array $typeMappingGroup = [
        UserGroup::GROUP_SUPPLIER => self::TYPE_SUPPLIER,
        UserGroup::GROUP_BUYER => self::TYPE_BUYER,
    ];

    public function isMainOwner(User $user): bool
    {
        return $this->user_id === $user->getId();
    }

    public function isEqualOwner(User $user): bool
    {
        $users = $this->getCompanyUsersObject();
        return $this->user_id === $user->getId() || in_array($user, $users);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === CompanyVerificationRequest::STATUS_VERIFIED;
    }

    public function getVerificationStatusCaption(): string
    {
        return CompanyVerificationRequest::$statusCaptions[$this->verification_status] ?? '';
    }

    public function getSiteLink(): string
    {
        return <<<EOT
        <a href="{$this->site}" target="_blank">{$this->site}</a>
EOT;

    }

    public function getCompanyUsersObject(): array
    {
        return array_map(function (CompanyUser $companyUser) { return $companyUser->getUser(); }, $this->getCompanyUsers(CompanyUserQuery::create()->filterByActive(true))->getData());
    }

    public function getCompanyUsersData(): array
    {
        $users = $this->getCompanyUsersObject();
        $users[] = $this->getUserRelatedByUserId();

        return array_filter(array_unique($users));
    }

    public function isSupplierCompany(): bool
    {
        return $this->type === self::TYPE_SUPPLIER;
    }

    public function isBuyerCompany(): bool
    {
        return $this->type === self::TYPE_BUYER;
    }

    public function getTypeObject(): array
    {
        return [
            'id' => $this->type,
            'title' => self::$typeCaptions[$this->type] ?? '',
            'code' => self::$typeCodes[$this->type] ?? '',
        ];
    }

    public static function getTypeByCode($code): int
    {
        $flip = array_flip(self::$typeCodes);
        return $flip[$code] ?? self::TYPE_SUPPLIER;
    }

    public function getMercurySetting(ConnectionInterface $con = null): MercurySetting
    {
        $setting = parent::getMercurySetting($con);

        if (!$setting) {
            $setting = new MercurySetting();
            $setting->setCompany($this)->save();
        }

        return $setting;
    }

    public function getDiadocSetting(ConnectionInterface $con = null): DiadocSetting
    {
        $setting = parent::getDiadocSetting($con);

        if (!$setting) {
            $setting = new DiadocSetting();
            $setting->setCompany($this)->save();
        }

        return $setting;
    }

    public function getDocrobotSetting(ConnectionInterface $con = null): DocrobotSetting
    {
        $setting = parent::getDocrobotSetting($con);

        if (!$setting) {
            $setting = new DocrobotSetting();
            $setting->setCompany($this)->save();
        }

        return $setting;
    }

    public function getIikoSetting(ConnectionInterface $con = null): IikoSetting
    {
        $setting = parent::getIikoSetting($con);

        if (!$setting) {
            $setting = new IikoSetting();
            $setting->setCompany($this)->save();
        }

        return $setting;
    }

    public function getStoreHouseSetting(ConnectionInterface $con = null): StoreHouseSetting
    {
        $setting = parent::getStoreHouseSetting($con);

        if (!$setting) {
            $setting = new StoreHouseSetting();
            $setting->setCompany($this)->save();
        }

        return $setting;
    }

    public function completeFirstImportSmart(): self
    {
        $this->setFirstImportSmartCompleted(true)->save();

        return $this;
    }

    public function getExternalCode(): ?string
    {
        $codes = array_filter([$this->diadoc_external_code, $this->docrobot_external_code, $this->storehouse_external_code]);
        return array_shift($codes);
    }
}
