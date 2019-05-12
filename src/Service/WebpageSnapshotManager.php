<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Web\Cache;
use App\Utils\Web\UrlFetcher;
use App\Utils\Web\UrlFetcherException;
use App\Utils\Web\WebpageSnapshot;
use DateTime;
use DateTimeZone;
use Exception;

class WebpageSnapshotManager
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var UrlFetcher
     */
    private $fetcher;

    public function __construct(string $projectDir)
    {
        $this->cache = new Cache("$projectDir/var/snapshots");
        $this->fetcher = new UrlFetcher();
    }

    public function clearCache(): void
    {
        $this->cache->clear();
    }

    public function get(string $url): WebpageSnapshot
    {
        return $this->cache->getOrSet($url, function () use ($url): WebpageSnapshot {
            return $this->download($url);
        });
    }

    /**
     * @param string $url
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     * @throws Exception
     */
    private function download(string $url): WebpageSnapshot
    {
        return new WebpageSnapshot($url, $this->fetcher->get($url), new DateTime('now', new DateTimeZone('UTC')));
    }
}
