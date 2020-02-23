<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Web\DelayAwareUrlFetchingQueue;
use App\Utils\Web\DependencyUrl;
use App\Utils\Web\Fetchable;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\WebpageSnapshot;
use App\Utils\Web\WebpageSnapshotCache;
use App\Utils\Web\WebsiteInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class WebpageSnapshotManager
{
    private WebpageSnapshotCache $cache;
    private GentleHttpClient $httpClient;
    private LoggerInterface $logger;

    public function __construct(string $projectDir, LoggerInterface $logger)
    {
        $this->cache = new WebpageSnapshotCache("$projectDir/var/snapshots");
        $this->httpClient = new GentleHttpClient();
        $this->logger = $logger;
    }

    /**
     * @throws ExceptionInterface
     */
    public function get(Fetchable $url, bool $refetch): WebpageSnapshot
    {
        if ($refetch || (null === ($result = $this->cache->get($url)))) {
            try {
                $result = $this->fetch($url);

                $url->recordSuccessfulFetch();

                $this->cache->set($url, $result);
            } catch (ExceptionInterface $exception) {
                $this->logger->debug('Failed fetching: '.$url);

                $url->recordFailedFetch($exception->getCode(), $exception->getMessage());

                throw $exception;
            }
        } else {
            $this->logger->debug('Retrieved from cache: '.$url);
        }

        return $result;
    }

    /**
     * @param Fetchable[] $urls
     */
    public function prefetchUrls(array $urls, bool $refetch, StyleInterface $progressReportIo): void
    {
        $urls = array_filter($urls, function (Fetchable $url): bool {
            return !$this->cache->has($url);
        });

        $queue = new DelayAwareUrlFetchingQueue($urls, GentleHttpClient::DELAY_FOR_HOST_MILLISEC);

        $progressReportIo->progressStart(count($urls));

        while (($url = $queue->pop())) {
            try {
                $this->get($url, $refetch);
            } catch (ExceptionInterface $exception) {
                // Prefetching = keep quiet, we'll retry
            }

            $progressReportIo->progressAdvance();
        }

        $progressReportIo->progressFinish();
    }

    /**
     * @throws ExceptionInterface
     */
    private function fetch(Fetchable $url): WebpageSnapshot
    {
        $this->logger->debug('Fetching: '.$url);

        if ($url->isDependency()) {
            $response = $this->httpClient->getImmediately($url->getUrl());
        } else {
            $response = $this->httpClient->get($url->getUrl(), );
        }

        $this->logger->debug('Sent request: '.$url);

        $webpageSnapshot = new WebpageSnapshot($url->getUrl(), $response->getContent(true),
            DateTimeUtils::getNowUtc(), $url->getOwnerName());

        $this->logger->debug('Fetched: '.$url);

        $this->fetchChildren($webpageSnapshot, $url);

        return $webpageSnapshot;
    }

    /**
     * @throws ExceptionInterface
     */
    private function fetchChildren(WebpageSnapshot $webpageSnapshot, Fetchable $url): void
    {
        foreach (WebsiteInfo::getChildrenUrls($webpageSnapshot) as $childUrl) {
            $webpageSnapshot->addChild($this->fetch(new DependencyUrl($childUrl, $url)));
        }
    }
}
