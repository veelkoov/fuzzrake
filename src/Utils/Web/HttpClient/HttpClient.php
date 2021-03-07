<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClient
{
    private const CONNECTION_TIMEOUT_SEC = 10;
    private const TIMEOUT_SEC = 30;

    private const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.8; Symfony HttpClient/Curl; +https://getfursu.it/)';

    private CurlHttpClient $client;

    public function __construct()
    {
        $this->client = new CurlHttpClient([
            'headers' => [
                'User-Agent'      => self::USER_AGENT,
            ],
            'http_version' => 1.1, // 2.0 likes to Segmentation fault (core dumped)
            'timeout'      => self::CONNECTION_TIMEOUT_SEC,
            'max_duration' => self::TIMEOUT_SEC,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function get(string $url, CookieJar $cookieJar = null, array $additionalHeaders = []): ResponseInterface
    {
        $options = [
            'headers' => $this->appendCookieToHeaders($additionalHeaders, $cookieJar, $url),
        ];

        return $this->client->request('GET', $url, $options);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function post(string $url, string $payload, CookieJar $cookieJar, array $additionalHeaders = []): ResponseInterface
    {
        return $this->client->request('POST', $url, [
            'body'    => $payload,
            'headers' => $this->appendCookieToHeaders($additionalHeaders, $cookieJar, $url),
        ]);
    }

    /**
     * @param string[] $headers
     *
     * @return string[]
     *
     * @see HttpBrowser::getHeaders
     */
    private function appendCookieToHeaders(array $headers, ?CookieJar $cookieJar, string $url): array
    {
        if ($cookieJar) {
            $cookies = [];

            foreach ($cookieJar->allRawValues($url) as $name => $value) {
                $cookies[] = $name.'='.$value;
            }

            if ($cookies) {
                $headers['cookie'] = implode('; ', $cookies);
            }
        }

        return $headers;
    }
}
