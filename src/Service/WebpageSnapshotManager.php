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
    public function __construct(
        private readonly GentleHttpClient $httpClient,
        private readonly WebpageSnapshotCache $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function get(Fetchable $url, bool $refetch): WebpageSnapshot
    {
        if (!$refetch && (null !== ($result = $this->cache->get($url)))) {
            $this->logger->debug("Retrieved from cache: $url");

            return $result;
        }

        $result = $this->fetch($url);
        $this->cache->set($url, $result);

        $this->updateUrlHealthStatus($url, $result);

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
            $this->get($url, $refetch);

            $progressReportIo->progressAdvance();
        }

        $progressReportIo->progressFinish();
    }

    private function fetch(Fetchable $url): WebpageSnapshot
    {
        $response = null;
        $code = null;
        $content = null;
        $headers = null;
        $errors = [];

        $this->logger->debug("Will request: $url");

        try {
            $response = $this->getDependencyAware($url);
        } catch (ExceptionInterface $ex) {
            $this->logger->info("Exception during fetching: $url", ['exception' => $ex]);
            $errors[] = $ex->getMessage();
        }

        $this->logger->debug("Finished requesting: $url");

        try {
            $code = $response?->getStatusCode();
        } catch (ExceptionInterface $ex) {
            $this->logger->info("Exception during getting status code for: $url", ['exception' => $ex]);
            $errors[] = $ex->getMessage();
        }

        try {
            $content = $response?->getContent();
        } catch (ExceptionInterface $ex) {
            $this->logger->info("Exception during getting content for: $url", ['exception' => $ex]);
            $errors[] = $ex->getMessage();
        }

        try {
            $headers = $response?->getHeaders();
        } catch (ExceptionInterface $ex) {
            $this->logger->info("Exception during getting headers for: $url", ['exception' => $ex]);
            $errors[] = $ex->getMessage();
        }

        if (200 === $code && null !== ($latentCode = WebsiteInfo::getLatentCode($url->getUrl(), $content))) {
            $this->logger->info("Correcting response code for $url from $code to $latentCode");
            $code = $latentCode;
        }

        $webpageSnapshot = new WebpageSnapshot($url->getUrl(), $content ?? '', DateTimeUtils::getNowUtc(),
            $url->getOwnerName(), $code ?? 0, $headers ?? [], array_unique($errors));

        $this->fetchChildren($webpageSnapshot, $url);

        return $webpageSnapshot;
    }

    private function fetchChildren(WebpageSnapshot $webpageSnapshot, Fetchable $url): void
    {
        foreach (WebsiteInfo::getChildrenUrls($webpageSnapshot) as $childUrl) {
            $webpageSnapshot->addChild($this->fetch(new DependencyUrl($childUrl, $url)));
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

    private function updateUrlHealthStatus(Fetchable $url, ?WebpageSnapshot $snapshot): void
    {
        if ($snapshot && $snapshot->isOK()) {
            $url->recordSuccessfulFetch();
        } else {
            $code = $snapshot->getHttpCode();
            $message = implode(' / ', $snapshot->getErrors());

            $url->recordFailedFetch($code, $message);
            $this->logger->debug("Failed fetching '$url' with code $code: $message");
        }
    }
}
