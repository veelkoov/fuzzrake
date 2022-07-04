<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\UrlUtils;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GentleHttpClient extends HttpClient
{
    final public const DELAY_FOR_HOST_MILLISEC = 10000;

    /**
     * Host => Last request since Epoch [ms].
     *
     * @var int[]
     */
    private array $lastRequestsMs = [];

    /**
     * {@inheritDoc}
     */
    public function get(string $url, CookieJar $cookieJar = null, array $additionalHeaders = []): ResponseInterface
    {
        $this->delayForHost($url);

        return $this->getImmediately($url, $cookieJar, $additionalHeaders);
    }

    /**
     * {@inheritDoc}
     */
    public function post(string $url, string $payload, CookieJar $cookieJar, array $additionalHeaders = []): ResponseInterface
    {
        $this->delayForHost($url);

        return $this->postImmediately($url, $payload, $cookieJar, $additionalHeaders);
    }

    /**
     * @param array<string, string> $additionalHeaders
     *
     * @throws TransportExceptionInterface
     */
    public function getImmediately(string $url, CookieJar $cookieJar = null, array $additionalHeaders = []): ResponseInterface
    {
        try {
            return parent::get($url, $cookieJar, $additionalHeaders);
        } finally {
            $this->updateLastHostCall($url);
        }
    }

    /**
     * @param array<string, string> $additionalHeaders
     *
     * @throws TransportExceptionInterface
     */
    public function postImmediately(string $url, string $payload, CookieJar $cookieJar, array $additionalHeaders = []): ResponseInterface
    {
        try {
            return parent::post($url, $payload, $cookieJar, $additionalHeaders);
        } finally {
            $this->updateLastHostCall($url);
        }
    }

    private function delayForHost(string $url): void
    {
        $host = UrlUtils::hostFromUrl($url);

        if (array_key_exists($host, $this->lastRequestsMs)) {
            $millisecondsToWait = $this->lastRequestsMs[$host] + self::DELAY_FOR_HOST_MILLISEC - UtcClock::timems();

            if ($millisecondsToWait > 0) {
                usleep($millisecondsToWait * 1000);
            }
        }
    }

    private function updateLastHostCall(string $url): void
    {
        $this->lastRequestsMs[UrlUtils::hostFromUrl($url)] = UtcClock::timems();
    }
}
