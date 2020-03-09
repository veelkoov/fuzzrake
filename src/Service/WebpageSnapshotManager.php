<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Web\DelayAwareUrlFetchingQueue;
use App\Utils\Web\DependencyUrl;
use App\Utils\Web\Fetchable;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\Snapshot\WebpageSnapshot;
use App\Utils\Web\Snapshot\WebpageSnapshotCache;
use App\Utils\Web\WebsiteInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WebpageSnapshotManager
{
    private WebpageSnapshotCache $cache;
    private GentleHttpClient $httpClient;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, WebpageSnapshotCache $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->httpClient = new GentleHttpClient();
    }

    /**
     * @throws ExceptionInterface
     */
    public function get(Fetchable $url, bool $refetch, bool $throw): WebpageSnapshot
    {
        if (!$refetch && (null !== ($result = $this->cache->getOK($url)))) {
            $this->logger->debug('Retrieved from cache: '.$url);

            return $result;
        }

        try {
            $result = $this->fetch($url, $throw);
            $this->updateUrlHealthStatus($url, $result, null);

            $this->cache->set($url, $result);
        } catch (ExceptionInterface $exception) {
            $this->updateUrlHealthStatus($url, null, $exception);

            throw $exception;
        }

        return $result;
    }

    /**
     * @param Fetchable[] $urls
     */
    public function prefetchUrls(array $urls, bool $refetch, StyleInterface $progressReportIo): void
    {
        $queue = new DelayAwareUrlFetchingQueue($urls, GentleHttpClient::DELAY_FOR_HOST_MILLISEC);

        $progressReportIo->progressStart(count($urls));

        while (($url = $queue->pop())) {
            try {
                $this->get($url, $refetch, false);
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
    private function fetch(Fetchable $url, bool $throw): WebpageSnapshot
    {
        $this->logger->debug('Will request: '.$url);
        $response = $this->getDependencyAware($url);
        $this->logger->debug('Sent request: '.$url);

        $code = $response->getStatusCode();
        $content = $response->getContent($throw);

        if (200 === $code && null !== ($latentCode = WebsiteInfo::getLatentCode($url->getUrl(), $content))) {
            $this->logger->info("Correcting response code for $url from $code to $latentCode");
            $code = $latentCode;
        }

        $webpageSnapshot = new WebpageSnapshot($url->getUrl(), $content, DateTimeUtils::getNowUtc(),
            $url->getOwnerName(), $code, $response->getHeaders($throw));

        $this->logger->debug('Received response: '.$url);

        $this->fetchChildren($webpageSnapshot, $url, $throw);

        return $webpageSnapshot;
    }

    /**
     * @throws ExceptionInterface
     */
    private function fetchChildren(WebpageSnapshot $webpageSnapshot, Fetchable $url, bool $throw): void
    {
        foreach (WebsiteInfo::getChildrenUrls($webpageSnapshot) as $childUrl) {
            $webpageSnapshot->addChild($this->fetch(new DependencyUrl($childUrl, $url), $throw));
        }
    }

    /**
     * @throws ExceptionInterface
     */
    private function getDependencyAware(Fetchable $url): ResponseInterface
    {
        if ($url->isDependency()) {
            return $this->httpClient->getImmediately($url->getUrl());
        } else {
            return $this->httpClient->get($url->getUrl());
        }
    }

    private function updateUrlHealthStatus(Fetchable $url, ?WebpageSnapshot $snapshot, ?ExceptionInterface $exception): void
    {
        if ($snapshot && $snapshot->isOK()) {
            $url->recordSuccessfulFetch();
        } else {
            $code = $exception ? $exception->getCode() : $snapshot->getHttpCode();
            $message = $exception ? $exception->getMessage() : "HTTP {$code} returned for \"{$url->getUrl()}\"";

            $url->recordFailedFetch($code, $message);
            $this->logger->debug("Failed fetching: {$url} {$message}");
        }
    }
}
