<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Web\DelayAwareUrlFetchingQueue;
use App\Utils\Web\DependencyUrl;
use App\Utils\Web\Fetchable;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\HttpClient\HttpClientException;
use App\Utils\Web\WebpageSnapshot;
use App\Utils\Web\WebpageSnapshotCache;
use App\Utils\Web\WebsiteInfo;
use Symfony\Component\Console\Style\StyleInterface;

class WebpageSnapshotManager
{
    private WebpageSnapshotCache $cache;
    private GentleHttpClient $httpClient;

    public function __construct(string $projectDir)
    {
        $this->cache = new WebpageSnapshotCache("$projectDir/var/snapshots");
        $this->httpClient = new GentleHttpClient();
    }

    public function clearCache(): void
    {
        $this->cache->clear();
    }

    /**
     * @throws HttpClientException from inside fetch()
     */
    public function get(Fetchable $url): WebpageSnapshot
    {
        return $this->cache->getOrSet($url, function () use ($url) {
            try {
                $result = $this->fetch($url);
                $url->recordSuccessfulFetch();

                return $result;
            } catch (HttpClientException $exception) {
                $url->recordFailedFetch($exception->getCode(), $exception->getMessage());
                throw $exception;
            }
        });
    }

    /**
     * @param Fetchable[] $urls
     */
    public function prefetchUrls(array $urls, StyleInterface $progressReportIo): void
    {
        $urls = array_filter($urls, function (Fetchable $url): bool {
            return !$this->cache->has($url);
        });

        $queue = new DelayAwareUrlFetchingQueue($urls, GentleHttpClient::DELAY_FOR_HOST_MILLISEC);

        $progressReportIo->progressStart(count($urls));

        while (($url = $queue->pop())) {
            try {
                $this->get($url);
            } catch (HttpClientException $exception) {
                // Prefetching = keep quiet, we'll retry
            }

            $progressReportIo->progressAdvance();
        }

        $progressReportIo->progressFinish();
    }

    /**
     * @throws HttpClientException
     */
    private function fetch(Fetchable $url): WebpageSnapshot
    {
        if ($url->isDependency()) {
            $contents = $this->httpClient->getImmediately($url->getUrl());
        } else {
            $contents = $this->httpClient->get($url->getUrl());
        }

        $webpageSnapshot = new WebpageSnapshot($url->getUrl(), $contents,
            DateTimeUtils::getNowUtc(), $url->getOwnerName());

        $this->fetchChildren($webpageSnapshot, $url);

        return $webpageSnapshot;
    }

    /**
     * @throws HttpClientException
     */
    private function fetchChildren(WebpageSnapshot $webpageSnapshot, Fetchable $url): void
    {
        foreach (WebsiteInfo::getChildrenUrls($webpageSnapshot) as $childUrl) {
            $webpageSnapshot->addChild($this->get(new DependencyUrl($childUrl, $url)));
        }
    }
}
