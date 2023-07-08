<?php

declare(strict_types=1);

namespace App\Service;

use App\Tracking\Web\HttpClient\GentleHttpClient;
use App\Tracking\Web\Url\Fetchable;
use App\Tracking\Web\WebpageSnapshot\Cache;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Tracking\Web\WebsiteInfo;
use App\Utils\DateTime\UtcClock;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WebpageSnapshotManager
{
    private readonly WebsiteInfo $websiteInfo;

    public function __construct(
        private readonly GentleHttpClient $httpClient,
        private readonly Cache $cache,
        private readonly LoggerInterface $logger,
    ) {
        $this->websiteInfo = new WebsiteInfo();
    }

    public function get(Fetchable $url, bool $refetch): Snapshot
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

    private function fetch(Fetchable $url): Snapshot
    {
        $response = null;
        $code = null;
        $content = '';
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
            $content = $response?->getContent() ?? '';
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

        if (200 === $code && null !== ($latentCode = $this->websiteInfo->getLatentCode($url->getUrl(), $content))) {
            $this->logger->info("Correcting response code for $url from $code to $latentCode");
            $code = $latentCode;
        }

        $webpageSnapshot = new Snapshot($content, $url->getUrl(), UtcClock::now(),
            $url->getOwnerName(), $code ?? 0, $headers ?? [], array_unique($errors));

        $this->fetchChildren($webpageSnapshot, $url);

        return $webpageSnapshot;
    }

    private function fetchChildren(Snapshot $webpageSnapshot, Fetchable $url): void
    {
        // Here be no longer even dragons
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

    private function updateUrlHealthStatus(Fetchable $url, Snapshot $snapshot): void
    {
        if ($snapshot->isOK()) {
            $url->recordSuccessfulFetch();
        } else {
            $code = $snapshot->httpCode;
            $message = implode(' / ', $snapshot->errors);

            $url->recordFailedFetch($code, $message);
            $this->logger->debug("Failed fetching '$url' with code $code: $message");
        }
    }
}
