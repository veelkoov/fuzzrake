<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Url\Url;
use Veelkoov\Debris\Maps\StringToInt;

class HostCallsQueue
{
    /**
     * Key = hostname. Value = Next allowed call timestamp (epoch sec).
     */
    private readonly StringToInt $hostToTimestamp;

    public function __construct(
        private readonly int $delayForHostSec,
    ) {
        $this->hostToTimestamp = new StringToInt();
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
        $host = UrlUtils::getHost($url->getUrl());

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
}
