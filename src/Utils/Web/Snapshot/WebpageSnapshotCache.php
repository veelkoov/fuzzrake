<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshot;

use App\Utils\DateTime\DateTimeException;
use App\Utils\Web\Fetchable;
use App\Utils\Web\UrlUtils;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\IOException;

class WebpageSnapshotCache
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $cacheDirPath,
    ) {
    }

    public function has(Fetchable $url): bool
    {
        return is_dir($this->getBaseDir($url->getUrl()));
    }

    public function get(Fetchable $url): ?WebpageSnapshot
    {
        if (!$this->has($url)) {
            return null;
        }

        try {
            return WebpageSnapshotJar::load($this->getBaseDir($url->getUrl()));
        } catch (JsonException|DateTimeException|InvalidArgumentException $e) {
            $this->logger->warning('Failed reading snapshot from cache', ['url' => $url, 'exception' => $e]);

            return null;
        }
    }

    public function set(Fetchable $url, WebpageSnapshot $snapshot): void
    {
        try {
            WebpageSnapshotJar::dump($this->getBaseDir($url->getUrl()), $snapshot);
        } catch (JsonException|IOException $e) {
            $this->logger->warning('Failed saving snapshot into cache', ['url' => $url, 'exception' => $e]);
        }
    }

    private function getBaseDir(string $url): string
    {
        $hostName = pattern('^www\.')->prune(UrlUtils::hostFromUrl($url));

        $urlFsSafe = UrlUtils::safeFileNameFromUrl($url);
        if (str_starts_with($urlFsSafe, $hostName)) {
            $urlFsSafe = substr($urlFsSafe, strlen($hostName));
        }

        $urlHash = hash('sha224', $url);

        return "$this->cacheDirPath/$hostName/$urlFsSafe-$urlHash";
    }
}
