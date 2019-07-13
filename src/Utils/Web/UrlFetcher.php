<?php

declare(strict_types=1);

namespace App\Utils\Web;

class UrlFetcher
{
    private $lastRequests = [];

    const DELAY_FOR_HOST_SEC = 5;
    const CONNECTION_TIMEOUT_SEC = 10;

    const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.5; +http://getfursu.it/)';

    public function get(string $url): string
    {
        $ch = $this->getCurlSessionHandle($url);

        if (false === $ch) {
            throw new RuntimeUrlFetcherException('Failed to initialize CURL');
        }

        $this->delayForHost($url);
        $result = curl_exec($ch);
        $this->updateLastHostCall($url);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg = curl_error($ch);
        curl_close($ch);

        if (false === $result || !in_array($httpCode, [200, 401])) {
            throw new UrlFetcherException("Failed to fetch URL ($httpCode): $url, ".($errorMsg ?: 'CURL failed'));
        }

        return $result;
    }

    private function delayForHost(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (array_key_exists($host, $this->lastRequests)) {
            $this->waitUntil($this->lastRequests[$host], self::DELAY_FOR_HOST_SEC);
        }
    }

    private function updateLastHostCall(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        $this->lastRequests[$host] = time();
    }

    private function waitUntil($basetime, $delay): void
    {
        $secondsToWait = $basetime + $delay - time();

        if ($secondsToWait > 0) {
            sleep($secondsToWait);
        }
    }

    /**
     * @param string $url
     *
     * @return resource
     */
    private function getCurlSessionHandle(string $url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT_SEC);
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'identity');

        if (WebsiteInfo::isFurAffinity($url, null) && !empty($_ENV['FA_COOKIE'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $_ENV['FA_COOKIE']);
        }

        return $ch;
    }
}
