<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Regexp\Utils as Regexp;
use Closure;
use JsonException;
use Symfony\Component\Filesystem\Filesystem;

class WebpageSnapshotCache
{
    private string $cacheDirPath;
    private Filesystem $fs;

    public function __construct(string $cacheDirPath)
    {
        $this->cacheDirPath = $cacheDirPath;

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->cacheDirPath);
    }

    public function clear()
    {
        $this->fs->remove($this->cacheDirPath);
        $this->fs->mkdir($this->cacheDirPath);
    }

    public function getOrSet(Url $url, Closure $getUrl): WebpageSnapshot
    {
        $snapshotPath = $this->snapshotPathForUrl($url);

        if ($this->cacheItemExists($snapshotPath)) {
            try {
                return $this->get($snapshotPath);
            } catch (JsonException $e) {
                // TODO: Log exception; snapshot was corrupted
                // Fallback below
            }
        }

        return $this->put($snapshotPath, $getUrl());
    }

    public function has(Url $url): bool
    {
        return $this->cacheItemExists($this->snapshotPathForUrl($url));
    }

    private function cacheItemExists(string $snapshotPath): bool
    {
        return file_exists($snapshotPath);
    }

    /**
     * @throws JsonException
     */
    private function get(string $snapshotPath): WebpageSnapshot
    {
        return WebpageSnapshot::fromJson(file_get_contents($snapshotPath));
    }

    private function put(string $snapshotPath, WebpageSnapshot $snapshot): WebpageSnapshot
    {
        $this->fs->mkdir(dirname($snapshotPath));

        try {
            $this->fs->dumpFile($snapshotPath, $snapshot->toJson());
        } catch (JsonException $e) {
            // TODO: Add logging/notifying
        }

        return $snapshot;
    }

    private function snapshotPathForUrl(Url $url): string
    {
        $host = Regexp::replace('#^www\.#', '', UrlUtils::hostFromUrl($url->getUrl()));
        $hash = hash('sha512', $url->getUrl());

        return "{$this->cacheDirPath}/{$host}/{$this->urlToFilename($url->getUrl())}-$hash.json";
    }

    private function urlToFilename(string $url): string
    {
        return trim(
            Regexp::replace('#[^a-z0-9_.-]+#i', '_',
                Regexp::replace('~^https?://(www\.)?|(\?|#).+$~', '', $url)
            ), '_');
    }
}
