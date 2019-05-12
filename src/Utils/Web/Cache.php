<?php

declare(strict_types=1);

namespace App\Utils\Web;

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
            return $this->get($snapshotPath);
        } else {
            return $this->put($snapshotPath, $getUrl());
        }
    }

    private function has(string $snapshotPath)
    {
        return file_exists($snapshotPath);
    }

    private function get(string $snapshotPath): WebpageSnapshot
    {
        return WebpageSnapshot::fromJson(file_get_contents($snapshotPath));
    }

    private function put(string $snapshotPath, WebpageSnapshot $snapshot): WebpageSnapshot
    {
        $this->fs->mkdir(dirname($snapshotPath));
        $this->fs->dumpFile($snapshotPath, $snapshot->toJson());

        return $snapshot;
    }

    private function snapshotPathForUrl(string $url): string
    {
        $host = preg_replace('#^www\.#', '', parse_url($url, PHP_URL_HOST)) ?: 'unknown_host';
        $hash = sha1($url);

        return "{$this->cacheDirPath}/{$host}/{$this->urlToFilename($url)}-$hash.json";
    }

    private function urlToFilename(string $url): string
    {
        return trim(
            preg_replace('#[^a-z0-9_.-]+#i', '_',
                preg_replace('~^https?://(www\.)?|(\?|#).+$~', '', $url)
            ), '_');
    }
}
