<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Regexp\Regexp;
use App\Utils\Web\DelayAwareUrlFetchingQueue;
use App\Utils\Web\Fetchable;
use App\Utils\Web\GentleHttpClient;
use App\Utils\Web\HttpClientException;
use App\Utils\Web\Url;
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
     * @throws HttpClientException from inside download()
     */
    public function get(Fetchable $url): WebpageSnapshot
    {
        return $this->cache->getOrSet($url, function () use ($url) {
            return $this->download($url);
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
                // TODO: record failure
                // Prefetching = keep quiet, we'll retry
            }

            $progressReportIo->progressAdvance();
        }

        $progressReportIo->progressFinish();
    }

    /**
     * @throws HttpClientException
     */
    private function download(Fetchable $url): WebpageSnapshot
    {
        if ($url->isDependency()) {
            $contents = $this->httpClient->getImmediately($url->getUrl());
        } else {
            $contents = $this->httpClient->get($url->getUrl());
        }

        $webpageSnapshot = new WebpageSnapshot($url->getUrl(), $contents,
            DateTimeUtils::getNowUtc(), $url->getArtisan()->getName());

        $this->downloadChildren($webpageSnapshot, $url->getArtisan());

        return $webpageSnapshot;
    }

    /**
     * @throws HttpClientException
     */
    private function downloadChildren(WebpageSnapshot $webpageSnapshot, Artisan $artisan): void
    {
        if (WebsiteInfo::isWixsite($webpageSnapshot)) {
            $this->fetchWixsiteContents($webpageSnapshot, $artisan);
        } elseif (WebsiteInfo::isTrello($webpageSnapshot)) {
            $this->fetchTrelloContents($webpageSnapshot, $artisan);
        }
    }

    /**
     * @throws HttpClientException
     */
    private function fetchWixsiteContents(WebpageSnapshot $snapshot, Artisan $artisan): void
    {
        if (Regexp::matchAll(WebsiteInfo::WIXSITE_CHILDREN_REGEXP, $snapshot->getContents(), $matches)) {
            foreach ($matches['data_url'] as $dataUrl) {
                $snapshot->addChildren($this->get(new Url($dataUrl, $artisan, true)));
            }
        }
    }

    /**
     * @throws HttpClientException
     */
    private function fetchTrelloContents(WebpageSnapshot $snapshot, Artisan $artisan): void
    {
        if (!Regexp::match(WebsiteInfo::TRELLO_BOARD_URL_REGEXP, $snapshot->getUrl(), $matches)) {
            return;
        }

        $snapshot->addChildren($this->get(new Url(WebsiteInfo::getTrelloBoardDataUrl($matches['boardId']), $artisan, true)));
    }
}
