<?php

declare(strict_types=1);

namespace App\Tracking\Web;

use App\Utils\DateTime\UtcClock;

class HostCallsTiming
{
    /**
     * @var array<string, int> Hostname => Next allowed call timestamp (milliseconds)
     */
    private array $hostnames = [];

    public function __construct(
        private readonly int $millisecondsDelay,
    ) {
    }

    public function nextCallTimestampMs(string $hostname, int $timestampMsNow = null): int
    {
        return $this->hostnames[$hostname] ??= ($timestampMsNow ?? UtcClock::timems());
    }

    public function called(string $hostname): void
    {
        $this->hostnames[$hostname] = UtcClock::timems() + $this->millisecondsDelay;
    }
}
