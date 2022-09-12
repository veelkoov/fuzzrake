<?php

declare(strict_types=1);

namespace App\Tracking\Web;

use App\Tracking\Web\Url\Fetchable;
use App\Tracking\Web\Url\UrlUtils;
use App\Utils\DateTime\UtcClock;

class TimedUrlQueue
{
    /**
     * @var array<string, Fetchable[]> Hostname => urls
     */
    private array $urls = [];

    /**
     * @param Fetchable[] $urls
     */
    public function __construct(
        array $urls,
        private readonly HostCallsTiming $timing,
    ) {
        foreach ($urls as $url) {
            $this->addUrl($url);
        }
    }

    private function addUrl(Fetchable $url): void
    {
        $host = UrlUtils::hostFromUrl($url->getUrl());

        if (!array_key_exists($host, $this->urls)) {
            $this->urls[$host] = [];
        }

        $this->urls[$host][] = $url;
    }

    public function pop(): ?Fetchable
    {
        $this->sortHosts();

        $hostname = array_key_first($this->urls);

        if (null === $hostname) {
            return null;
        }

        $result = array_pop($this->urls[$hostname]);

        if ([] === $this->urls[$hostname]) {
            unset($this->urls[$hostname]);
        }

        return $result;
    }

    private function sortHosts(): void
    {
        $now = UtcClock::timems();

        uksort($this->urls, function (string $hostnameA, string $hostnameB) use ($now): int {
            $timestampDiff = $this->timing->nextCallTimestampMs($hostnameA, $now)
                <=> $this->timing->nextCallTimestampMs($hostnameB, $now);

            if (0 === $timestampDiff) {
                return count($this->urls[$hostnameB]) <=> count($this->urls[$hostnameA]);
            } else {
                return $timestampDiff;
            }
        });
    }
}
