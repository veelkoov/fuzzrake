<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\DateTime\DateTimeException;
use App\Utils\Regexp\Regexp;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class WebpageSnapshotCache
{
    private LoggerInterface $logger;
    private string $cacheDirPath;
    private Filesystem $fs;

    public function __construct(LoggerInterface $logger, string $snapshotCacheDirPath)
    {
        $this->logger = $logger;
        $this->cacheDirPath = $snapshotCacheDirPath;

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->cacheDirPath);
    }

    public function has(Fetchable $url): bool
    {
        return file_exists($this->snapshotPathForUrl($url->getUrl()));
    }

    public function get(Fetchable $url): ?WebpageSnapshot
    {
        if (!$this->has($url)) {
            return null;
        }

        try {
            return WebpageSnapshot::fromJson(file_get_contents($this->snapshotPathForUrl($url->getUrl())));
        } catch (JsonException | DateTimeException $e) {
            $this->logger->warning('Failed reading snapshot from cache', ['url' => $url, 'exception' => $e]);

            return null;
        }
    }

    public function getOK(Fetchable $url): ?WebpageSnapshot
    {
        $result = $this->get($url);

        return $result && $result->isOK() ? $result : null;
    }

    public function set(Fetchable $url, WebpageSnapshot $snapshot): void
    {
        $snapshotPath = $this->snapshotPathForUrl($url->getUrl());
        $this->fs->mkdir(dirname($snapshotPath));

        try {
            $this->fs->dumpFile($snapshotPath, $snapshot->toJson());
        } catch (JsonException $e) {
            $this->logger->warning('Failed saving snapshot into cache', ['url' => $url, 'exception' => $e]);
        }
    }

    private function snapshotPathForUrl(string $url): string
    {
        $host = Regexp::replace('#^www\.#', '', UrlUtils::hostFromUrl($url));
        $hash = hash('sha512', $url);

        return "{$this->cacheDirPath}/{$host}/{$this->urlToFilename($url)}-$hash.json";
    }

    private function urlToFilename(string $url): string
    {
        return trim(
            Regexp::replace('#[^a-z0-9_.-]+#i', '_',
                Regexp::replace('~^https?://(www\.)?|(\?|#).+$~', '', $url)
            ), '_');
    }
}
