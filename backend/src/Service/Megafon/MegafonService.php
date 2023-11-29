<?php

declare(strict_types=1);

namespace App\Service\Megafon;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class MegafonService
{
    const URL = 'https://a2p-api.megalabs.ru';

    private string $login;
    private string $password;
    private string $from;

    private ?Client $client = null;
    private LoggerInterface $smsLogger;

    public function __construct(string $login, string $password, string $from, LoggerInterface $smsLogger)
    {
        $this->login = $login;
        $this->password = $password;
        $this->from = $from;
        $this->smsLogger = $smsLogger;
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

    protected function getHeaders(): array
    {
        return array_merge($this->getAuthorizationHeader(), ['Content-Type' => 'application/json']);
    }

    public function send(string $phone, string $message): string
    {
        $data = json_encode([
            'from' => $this->from,
            'to' => (int) $phone,
            'message' => $message
        ]);

        $response = $this->getClient()->request(
            'POST',
            self::URL . '/sms/v1/sms',
            [
                'headers' => $this->getHeaders(),
                'body' => $data,
            ],
        );

        $content = $response->getBody()->getContents();

        $this->smsLogger->info(sprintf(
            'Request: [%s]. Response: [%s]',
            $data,
            $content
        ));

        return $content;
    }
}
