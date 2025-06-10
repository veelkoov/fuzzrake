<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Url\Url;
use Veelkoov\Debris\StringIntMap;

class HostCallsQueue
{
    /**
     * Key = hostname. Value = Next allowed call timestamp (epoch sec).
     */
    private readonly StringIntMap $hostToTimestamp;

    public function __construct(
        private readonly int $delayForHostSec,
    ) {
        $this->hostToTimestamp = new StringIntMap();
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function patiently(Url $url, callable $callback): mixed
    {
        $host = $this->getHost($url);

        $this->waitUtilCallAllowed($host);
        $result = $callback();
        $this->hostToTimestamp->set($host, UtcClock::time() + $this->delayForHostSec);

        return $result;
    }

    private function waitUtilCallAllowed(string $host): void
    {
        $waitUntilEpochSec = $this->hostToTimestamp->getOrDefault($host, static fn () => UtcClock::time());

        $secondsToWait = $waitUntilEpochSec - UtcClock::time();

        if ($secondsToWait > 0) {
            UtcClock::sleep($secondsToWait);
        }
    }

    private function getHost(Url $url): string
    {
        $result = parse_url($url->getUrl(), PHP_URL_HOST);

        return is_string($result) ? $result : '<parsing failed>';
    }
}
