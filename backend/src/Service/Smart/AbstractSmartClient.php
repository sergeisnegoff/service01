<?php

declare(strict_types=1);

namespace App\Service\Smart;

use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;

abstract class AbstractSmartClient
{
    const URL = 'https://cws.swnn.ru/WS/hs';

    private string $login;
    private string $password;
    private ?Client $client = null;

    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    protected function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    protected function getBase64Authorization(): string
    {
        return base64_encode(sprintf('%s:%s', $this->login, $this->password));
    }

    protected function getAuthorizationHeader(): array
    {
        return ['Authorization' => sprintf('Basic %s', $this->getBase64Authorization())];
    }

    protected function get($endpoint): StreamInterface
    {
        $response = $this->getClient()->request(
            'GET',
            self::URL . $endpoint,
            [
                'headers' => $this->getAuthorizationHeader()
            ]
        );

        return $response->getBody();
    }
}
