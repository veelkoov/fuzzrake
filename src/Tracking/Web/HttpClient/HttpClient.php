<?php

declare(strict_types=1);

namespace App\Tracking\Web\HttpClient;

use App\Tracking\Web\Detector;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClient
{
    private const CONNECTION_TIMEOUT_SEC = 10;
    private const TIMEOUT_SEC = 30;

    private const USER_AGENT_DEFAULT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.9; Symfony HttpClient/Curl; +https://getfursu.it/)';
    private const USER_AGENT_FOR_INSTAGRAM = 'Mozilla/5.0 (Linux; Android 8.1.0; motorola one Build/OPKS28.63-18-3; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/70.0.3538.80 Mobile Safari/537.36 Instagram 72.0.0.21.98 Android (27/8.1.0; 320dpi; 720x1362; motorola; motorola one; deen_sprout; qcom; pt_BR; 132081645)'; // Thank you https://github.com/postaddictme/instagram-php-scraper/pull/545

    private readonly CurlHttpClient $client;
    private readonly Detector $detector;

    public function __construct()
    {
        $this->client = new CurlHttpClient([
            'http_version' => 1.1, // 2.0 likes to Segmentation fault (core dumped)
            'timeout'      => self::CONNECTION_TIMEOUT_SEC,
            'max_duration' => self::TIMEOUT_SEC,
        ]);

        $this->detector = new Detector();
    }

    /**
     * @param array<string, string> $additionalHeaders
     *
     * @throws TransportExceptionInterface
     */
    public function get(string $url, CookieJar $cookieJar = null, array $additionalHeaders = []): ResponseInterface
    {
        $additionalHeaders['User-Agent'] = $this->getUserAgent($url);

        $options = [
            'headers' => $this->appendCookieToHeaders($additionalHeaders, $cookieJar, $url),
        ];

        return $this->client->request('GET', $url, $options);
    }

    /**
     * @param array<string, string> $additionalHeaders
     *
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

    private function getUserAgent(string $url): string
    {
        return $this->detector->isInstagram($url)
            ? self::USER_AGENT_FOR_INSTAGRAM
            : self::USER_AGENT_DEFAULT;
    }
}
