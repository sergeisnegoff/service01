<?php

declare(strict_types=1);

namespace App\Service\Iiko;

use App\Model\IikoSetting;
use GuzzleHttp\Client;
use LogicException;
use Psr\Http\Message\StreamInterface;
use Sabre\Xml as Xml;
use SimpleXMLElement;

class AbstractIikoClient
{
    public const SCHEME = 'https://';
    public const API_URL = 'resto/api';

    public const API_VERSION_2 = 'v2';

    public const
        FORMAT_RESPONSE_JSON = 'json',
        FORMAT_RESPONSE_XML = 'xml'
    ;

    private ?Client $client = null;
    private IikoSetting $setting;
    private Xml\Service $xmlService;
    private string $token = '';

    public function __construct()
    {
        $this->xmlService = new Xml\Service();
    }

    public function init(IikoSetting $setting)
    {
        $this->clearToken();
        $this->setting = $setting;
        $this->authorize();
    }

    protected function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    protected function buildUrl(string $endpoint, string $version = ''): string
    {
        $baseUrl = sprintf('%s%s/%s', self::SCHEME, $this->setting->getUrl(), self::API_URL);

        if ($version) {
            return implode('', [$baseUrl, DIRECTORY_SEPARATOR, $version, $endpoint]);
        }

        return implode('', [$baseUrl, $endpoint]);
    }

    protected function clearToken(): void
    {
        $this->token = '';
    }

    public function authorize(): void
    {
        if (!$this->token) {
            $this->token = $this->get('/auth', [
                'login' => $this->setting->getLogin(),
                'pass' => sha1($this->setting->getPassword())
            ])->getContents();
        }
    }

    public function logout(): void
    {
        if ($this->token) {
            $this->get('/logout');
            $this->clearToken();
        }
    }

    protected function buildQuery(array $fields = []): string
    {
        if ($this->token) {
            $fields['key'] = $this->token;
        }

        return implode(['?', http_build_query($fields)]);
    }

    protected function get(string $endpoint, array $fields = [], string $version = ''): StreamInterface
    {
        if (!$this->setting) {
            throw new LogicException('Method init not called');
        }

        $response = $this->getClient()->request(
            'GET',
            $this->buildUrl($endpoint, $version) . $this->buildQuery($fields)
        );

        if ($response->getStatusCode() === 401) {
            $this->clearToken();
            $this->authorize();

            return $this->get($endpoint, $fields);
        }

        return $response->getBody();
    }

    protected function postXml(string $endpoint, SimpleXMLElement $xml): StreamInterface
    {
        if (!$this->setting) {
            throw new LogicException('Method init not called');
        }

        $response = $this->getClient()->request(
            'POST',
            $this->buildUrl($endpoint) . $this->buildQuery(),
            [
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
                'body' => $this->doPrettyXml($xml),
            ],
        );

        if ($response->getStatusCode() === 401) {
            $this->clearToken();
            $this->authorize();

            return $this->post($endpoint);
        }

        return $response->getBody();
    }

    protected function normalizeResponse(StreamInterface $response, string $format = self::FORMAT_RESPONSE_JSON)
    {
        if ($format === self::FORMAT_RESPONSE_JSON) {
            return json_decode($response->getContents(), true);
        }

        return $this->xmlService->parse($response->getContents());
    }

    private function doPrettyXml(SimpleXMLElement $xml): string
    {
        $xml = str_replace("<?xml version=\"1.0\"?>\n", '', $xml->asXML());

        return html_entity_decode($xml, ENT_NOQUOTES, 'UTF-8');
    }
}
