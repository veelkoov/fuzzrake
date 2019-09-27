<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\JsonException;
use App\Utils\Regexp\Utils as Regexp;
use Closure;
use Symfony\Component\Filesystem\Filesystem;

class Cache
{
    /**
     * @var string
     */
    private $cacheDirPath;

    /**
     * @var Filesystem
     */
    private $fs;

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

    public function getOrSet(string $url, Closure $getUrl)
    {
        $snapshotPath = $this->snapshotPathForUrl($url);

        if ($this->has($snapshotPath)) {
            try {
                return $this->get($snapshotPath);
            } catch (JsonException $e) {
                // TODO: Log exception; snapshot was corrupted
                // Fallback below
            }
        }

        return $this->put($snapshotPath, $getUrl());
    }

    private function has(string $snapshotPath)
    {
        return file_exists($snapshotPath);
    }

    /**
     * @param string $snapshotPath
     *
     * @return WebpageSnapshot
     *
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

    private function snapshotPathForUrl(string $url): string
    {
        $host = Regexp::replace('#^www\.#', '', parse_url($url, PHP_URL_HOST)) ?: 'unknown_host';
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
