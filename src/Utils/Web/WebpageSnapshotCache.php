<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Regexp\Regexp;
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
        } catch (JsonException $e) {
            // TODO: INFO log
            return null;
        }
    }

    public function set(Fetchable $url, WebpageSnapshot $snapshot): void
    {
        $snapshotPath = $this->snapshotPathForUrl($url->getUrl());
        $this->fs->mkdir(dirname($snapshotPath));

        try {
            $this->fs->dumpFile($snapshotPath, $snapshot->toJson());
        } catch (JsonException $e) {
            // TODO: WARN log
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
