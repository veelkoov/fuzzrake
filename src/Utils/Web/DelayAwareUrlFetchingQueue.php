<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\DateTime\DateTimeUtils;

class DelayAwareUrlFetchingQueue
{
    private const NEXT_FETCH_TIMESTAMP = 'last_fetch_timestamp';
    private const URLS = 'urls';

    /**
     * @var Fetchable[]
     */
    private array $hosts = [];

    private int $millisecondsDelay;
    private ?string $lastHost = null;

    /**
     * @param Fetchable[] $urls
     */
    public function __construct(array $urls = [], int $millisecondsDelay = 0)
    {
        foreach ($urls as $url) {
            $this->addUrl($url);
        }

        $this->millisecondsDelay = $millisecondsDelay;
    }

    public function addUrl(Fetchable $url): void
    {
        $host = UrlUtils::hostFromUrl($url->getUrl());

        if (!array_key_exists($host, $this->hosts)) {
            $this->hosts[$host] = [
                self::NEXT_FETCH_TIMESTAMP => time(),
                self::URLS                 => [],
            ];
        }

        $this->hosts[$host][self::URLS][] = $url;
    }

    public function pop(): ?Fetchable
    {
        if (empty($this->hosts)) {
            $this->lastHost = null;

            return null;
        }

        if (array_key_exists($this->lastHost, $this->hosts)) {
            // Naively assume that next pop() call happens almost right after the previous URL got fetched
            $this->hosts[$this->lastHost][self::NEXT_FETCH_TIMESTAMP] = DateTimeUtils::timems() + $this->millisecondsDelay;
        }

        $this->sortHosts();

        reset($this->hosts);
        $host = key($this->hosts);
        $this->lastHost = $host;

        $result = array_pop($this->hosts[$host][self::URLS]);

        if (empty($this->hosts[$host][self::URLS])) {
            unset($this->hosts[$host]);
        }

        return $result;
    }

    private function sortHosts(): void
    {
        $now = DateTimeUtils::timems();

        uasort($this->hosts, function (array $a, array $b) use ($now): int {
            $timestampDiff = max(0, $a[self::NEXT_FETCH_TIMESTAMP] - $now) <=> max(0, $b[self::NEXT_FETCH_TIMESTAMP] - $now);

            if (0 === $timestampDiff) {
                return count($b[self::URLS]) <=> count($a[self::URLS]);
            } else {
                return $timestampDiff;
            }
        });
    }
}
