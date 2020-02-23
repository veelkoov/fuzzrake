<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\Web\CookieJar\CookieJarInterface;
use App\Utils\Web\CookieJar\NullCookieJar;
use App\Utils\Web\WebsiteInfo;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClient
{
    private const CONNECTION_TIMEOUT_SEC = 10;
    private const TIMEOUT_SEC = 30;

    private const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.8; Symfony HttpClient/Curl; +https://getfursu.it/)';

    private CookieJarInterface $cookieJar;
    private CurlHttpClient $client;

    public function __construct(CookieJarInterface $cookieJar = null)
    {
        $this->client = new CurlHttpClient([
            'headers' => [
                'User-Agent'      => self::USER_AGENT,
                'Accept-Encoding' => '*', // TODO: verify proper behavior, with native option '' = all supported
            ],
            'timeout'      => self::CONNECTION_TIMEOUT_SEC,
            'max_duration' => self::TIMEOUT_SEC,
        ]);

        $this->cookieJar = $cookieJar ?? new NullCookieJar(); // TODO: implement usage
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function get(string $url): ResponseInterface
    {
        $options = [
            'headers' => [],
        ];

        if (WebsiteInfo::isFurAffinity($url, null) && !empty($_ENV['FA_COOKIE'])) {
            $options['headers']['Cookie'] = $_ENV['FA_COOKIE']; // TODO: get rid of!
        }

        $response = $this->client->request('GET', $url, $options);

        // TODO: Correct latent 404s for FA, etc.

        return $response;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function post(string $url, string $payload, array $additionalHeaders = []): ResponseInterface
    {
        return $this->client->request('POST', $url, [
            'body'    => $payload,
            'headers' => $additionalHeaders,
        ]);
    }
}
