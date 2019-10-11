<?php

declare(strict_types=1);

namespace App\Utils\Web;

class HttpClient
{
    private const CONNECTION_TIMEOUT_SEC = 10;
    private const TIMEOUT_SEC = 30;

    private const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.7; +https://getfursu.it/)';

    /**
     * @var CookieJarInterface
     */
    private $cookieJar;

    public function __construct(CookieJarInterface $cookieJar = null)
    {
        if (null === $cookieJar) {
            $cookieJar = new NullCookieJar();
        }

        $this->cookieJar = $cookieJar;
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @throws HttpClientException
     */
    public function get(string $url): string
    {
        return $this->execute($url);
    }

    /**
     * @param string $url
     * @param string $payload
     * @param array  $additionalHeaders
     *
     * @return string
     *
     * @throws HttpClientException
     */
    public function post(string $url, string $payload, array $additionalHeaders = []): string
    {
        $additionalCurlOpts = [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $payload,
        ];

        if (!empty($additionalCurlOpts)) {
            foreach ($additionalHeaders as $header => $value) {
                $additionalHeaders[$header] = "$header: $value";
            }

            $additionalCurlOpts[CURLOPT_HTTPHEADER] = array_values($additionalHeaders);
        }

        return $this->execute($url, $additionalCurlOpts);
    }

    /**
     * @param string $url
     * @param array  $additionalCurlOpts
     *
     * @return bool|string
     *
     * @throws HttpClientException
     */
    private function execute(string $url, array $additionalCurlOpts = [])
    {
        $ch = $this->getCurlSessionHandle($url);

        if (false === $ch) {
            throw new RuntimeHttpClientException('Failed to initialize CURL');
        }

        if (!empty($additionalCurlOpts)) {
            curl_setopt_array($ch, $additionalCurlOpts);
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

        curl_setopt_array($ch, [
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_USERAGENT       => self::USER_AGENT,
            CURLOPT_CONNECTTIMEOUT  => self::CONNECTION_TIMEOUT_SEC,
            CURLOPT_TIMEOUT         => self::TIMEOUT_SEC,
            CURLOPT_ACCEPT_ENCODING => '', // = all supported
        ]);

        $this->cookieJar->setupFor($ch);

        if (WebsiteInfo::isFurAffinity($url, null) && !empty($_ENV['FA_COOKIE'])) { // TODO: get rid of!
            curl_setopt($ch, CURLOPT_COOKIE, $_ENV['FA_COOKIE']);
        }

        return $ch;
    }
}
