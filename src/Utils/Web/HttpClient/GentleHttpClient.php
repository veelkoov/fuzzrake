<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Web\UrlUtils;

class GentleHttpClient extends HttpClient
{
    public const DELAY_FOR_HOST_MILLISEC = 5000;

    /**
     * Host => Last request since Epoch [ms].
     *
     * @var int[]
     */
    private array $lastRequestsMs = [];

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

        if (array_key_exists($host, $this->lastRequestsMs)) {
            $millisecondsToWait = $this->lastRequestsMs[$host] + self::DELAY_FOR_HOST_MILLISEC - DateTimeUtils::timems();

            if ($millisecondsToWait > 0) {
                usleep($millisecondsToWait * 1000);
            }
        }
    }

    private function updateLastHostCall(string $url): void
    {
        $this->lastRequestsMs[UrlUtils::hostFromUrl($url)] = DateTimeUtils::timems();
    }
}
