<?php

declare(strict_types=1);

namespace App\Service\StoreHouse;

class StoreHouseClient extends AbstractStoreHouseClient
{
    public function getWarehouses()
    {
        return $this->post(self::PROCEDURE_DEPARTS)['shTable'] ?? [];
    }

    public function getUnits()
    {
        return $this->post(self::PROCEDURE_M_GROUPS)['shTable'] ?? [];
    }

    public function getProductCategories()
    {
        return $this->post(self::PROCEDURE_GOODS_CATEGORIES)['shTable'] ?? [];
    }

    public function getProductGroups()
    {
        return $this->post(self::PROCEDURE_G_GROUPS)['shTable'] ?? [];
    }

    public function getProducts(int $groupId)
    {
        $data = [
            'Input' => [[
                'head' => self::TABLE_PRODUCT_GROUPS,
                'original' => ['1'],
                'values' => [[$groupId]],
            ]],
        ];

        return $this->post(self::PROCEDURE_GOODS, $data)['shTable'] ?? [];
    }

    public function getCounterparties()
    {
        return $this->post(self::PROCEDURE_CORRS)['shTable'] ?? [];
    }

    public function getCurrencies()
    {
        return $this->post(self::PROCEDURE_CURRENCIES)['shTable'] ?? [];
    }

    public function addInvoice(array $dataSets)
    {
        $data = [
            'Input' => $dataSets,
        ];

        return $this->post(self::PROCEDURE_INS_G_DOC0, $data);
    }
}
