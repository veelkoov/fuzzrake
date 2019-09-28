<?php

declare(strict_types=1);

namespace App\Utils\Web;

class HttpClient
{
    const CONNECTION_TIMEOUT_SEC = 10;
    const TIMEOUT_SEC = 30;

    const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.7; +https://getfursu.it/)';

    /**
     * @param string $url
     *
     * @return string
     *
     * @throws HttpClientException
     */
    public function get(string $url): string
    {
        $ch = $this->getCurlSessionHandle($url);

        if (false === $ch) {
            throw new RuntimeUrlFetcherException('Failed to initialize CURL');
        }

        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg = curl_error($ch);
        $errorNo = curl_errno($ch);
        curl_close($ch);

        if (false === $result || !in_array($httpCode, [200, 401])) {
            throw new HttpClientException("Failed to fetch URL ($httpCode): $url, ".($errorMsg ?: 'CURL failed')." ($errorNo)");
        }

        return $result;
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
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_SEC);
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, ''); // = all supported

        if (WebsiteInfo::isFurAffinity($url, null) && !empty($_ENV['FA_COOKIE'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $_ENV['FA_COOKIE']);
        }

        return $ch;
    }
}
