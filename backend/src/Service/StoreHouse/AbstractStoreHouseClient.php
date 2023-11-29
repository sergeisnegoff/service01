<?php

declare(strict_types=1);

namespace App\Service\StoreHouse;

use App\Model\StoreHouseSetting;
use GuzzleHttp\Client;
use LogicException;
use Psr\Http\Message\StreamInterface;

abstract class AbstractStoreHouseClient
{
    public const
        SCHEME = 'http',
        ENDPOINT_EXEC = 'sh5exec'
    ;

    public const
        TABLE_WAREHOUSE = 106,
        TABLE_UNIT = 205,
        TABLE_CATEGORIES = 200,
        TABLE_PRODUCT_GROUPS = 209,
        TABLE_PRODUCTS = 210,
        TABLE_COUNTERPARTIES = 107,
        TABLE_INVOICE = 111,
        TABLE_INVOICE_PRODUCT = 112,
        TABLE_CURRENCIES = 100
    ;

    public const
        PROCEDURE_DEPARTS = 'Departs',
        PROCEDURE_M_GROUPS = 'MGroups',
        PROCEDURE_GOODS_CATEGORIES = 'GoodsCategories',
        PROCEDURE_G_GROUPS = 'GGroups',
        PROCEDURE_GOODS = 'Goods',
        PROCEDURE_CORRS = 'Corrs',
        PROCEDURE_INS_G_DOC0 = 'InsGDoc0',
        PROCEDURE_CURRENCIES = 'Currencies'
    ;

    private StoreHouseSetting $setting;
    private ?Client $client = null;

    public function init(StoreHouseSetting $setting)
    {
        $this->setting = $setting;
    }

    protected function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    protected function buildUrl(): string
    {
        return sprintf(
            '%s://%s:%s/api/%s',
            self::SCHEME,
            $this->setting->getIp(),
            $this->setting->getPort(),
            self::ENDPOINT_EXEC
        );
    }

    protected function post(string $procedure, array $data = [])
    {
        if (!$this->setting) {
            throw new LogicException('Method init not called');
        }

        $data['procName'] = $procedure;
        $data['UserName'] = $this->setting->getLogin();
        $data['Password'] = $this->setting->getPassword();

        $client = $this->getClient();
        $response = $client->request(
            'POST',
            $this->buildUrl(),
            [
                'body' => json_encode($data),
            ]
        );

        return $this->normalizeResponse($response->getBody());
    }

    protected function normalizeResponse(StreamInterface $response)
    {
        return json_decode($response->getContents(), true);
    }
}
