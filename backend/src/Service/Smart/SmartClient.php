<?php

declare(strict_types=1);

namespace App\Service\Smart;

use App\Model\Company;
use App\Service\Smart\Exception\SmartRequestException;
use GuzzleHttp\Exception\GuzzleException;

class SmartClient extends AbstractSmartClient
{
    public function getShops(string $phone): array
    {
        try {
            $response = $this->get(sprintf('/pl/getCustomers/%s', $phone));

        } catch (GuzzleException $exception) {
            throw new SmartRequestException('При получении торговых точек из SMART произошла ошибка');
        }

        return json_decode($response->getContents(), true);
    }
}
