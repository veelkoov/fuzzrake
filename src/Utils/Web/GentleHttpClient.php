<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\DateTimeUtils;

class GentleHttpClient extends HttpClient
{
    public const DELAY_FOR_HOST_MILLISEC = 5000;

    private $lastRequests = [];

    /**
     * @throws HttpClientException
     */
    public function get(string $url): string
    {
        $this->delayForHost($url);

        return $this->getImmediately($url);
    }

    /**
     * @throws HttpClientException
     */
    public function getImmediately(string $url): string
    {
        $result = parent::get($url);
        $this->updateLastHostCall($url);

        return $result;
    }

    private function delayForHost(string $url): void
    {
        $host = UrlUtils::hostFromUrl($url);

        if (array_key_exists($host, $this->lastRequests)) {
            $millisecondsToWait = $this->lastRequests[$host] + self::DELAY_FOR_HOST_MILLISEC - DateTimeUtils::timems();

            if ($millisecondsToWait > 0) {
                usleep($millisecondsToWait * 1000);
            }
        }
    }

    private function updateLastHostCall(string $url): void
    {
        $this->lastRequests[UrlUtils::hostFromUrl($url)] = time();
    }
}
