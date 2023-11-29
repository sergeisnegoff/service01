<?php

declare(strict_types=1);

namespace App\Service\Iiko;

use SimpleXMLElement;

class IikoClient extends AbstractIikoClient
{
    public function getWarehouses()
    {
        return $this->normalizeResponse($this->get('/corporation/stores'), self::FORMAT_RESPONSE_XML) ?? [];
    }

    public function getGroups()
    {
        return $this->normalizeResponse($this->get('/entities/products/group/list', [], self::API_VERSION_2)) ?? [];
    }

    public function getProducts()
    {
        return $this->normalizeResponse($this->get('/entities/products/list', [], self::API_VERSION_2)) ?? [];
    }

    public function getCounterparties()
    {
        return $this->normalizeResponse($this->get('/suppliers'), self::FORMAT_RESPONSE_XML) ?? [];
    }

    public function getUnits()
    {
        return $this->normalizeResponse($this->get('/entities/list', ['rootType' => 'MeasureUnit'], self::API_VERSION_2)) ?? [];
    }

    public function addInvoice(SimpleXMLElement $xml)
    {
        return $this->normalizeResponse($this->postXml('/documents/import/incomingInvoice', $xml), self::FORMAT_RESPONSE_XML);
    }
}
