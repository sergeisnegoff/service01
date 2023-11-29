<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Docrobot;

use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;

abstract class AbstractDocrobotClient
{
    const URL = 'https://api-service.e-vo.ru/Api';
    const VERSION = 'V1';

    const
        METHOD_AUTHORIZE = 'Edo/Index/Authorize',
        METHOD_GET_TIME_LINE = 'Edo/TimeLine/GetTimeLine',
        METHOD_GET_BOTH = 'Edo/Content/GetBoth',
        METHOD_GET_EDI_DOCS = 'Edo/Document/GetEdiDocs',
        METHOD_GET_EDI_DOC_BODY = 'Edo/Document/GetEdiDocBody'
    ;

    private ?string $token = null;
    private ?Client $client = null;
    private string $login;
    private string $password;

    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
        $this->authorize($login, $password);
    }

    protected function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    protected function buildUrl(string $endpoint): string
    {
        return sprintf('%s/%s/%s', self::URL, self::VERSION, $endpoint);
    }

    protected function authorize(string $login = '', string $password = ''): void
    {
        $response = $this->normalizeResponseContent($this->post(self::METHOD_AUTHORIZE, [
            'varLogin' => $login,
            'varPassword' => $password
        ]));

        $this->token = $response['varToken'];
    }

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function post(string $endpoint, array $body = []): StreamInterface
    {
        if ($this->token) {
            $body['varToken'] = $this->token;
        }

        $response = $this->getClient()->request(
            'POST',
            $this->buildUrl($endpoint),
            [
                'headers' => $this->getHeaders(),
                'body' => json_encode($body),
            ],
        );

        if ($response->getStatusCode() === 401) {
            $this->authorize($this->login, $this->password);
            return $this->post($endpoint, $body);
        }

        return $response->getBody();
    }

    protected function normalizeResponseContent(StreamInterface $response): array
    {
        return json_decode($response->getContents(), true);
    }
}
