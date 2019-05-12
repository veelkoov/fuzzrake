<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Web\UrlFetcher;
use App\Utils\Web\UrlFetcherException;
use App\Utils\Web\WebpageSnapshot;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\Filesystem\Filesystem;

class WebpageSnapshotManager
{
    private const JSON_SERIALIZATION_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_LINE_TERMINATORS | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR;

    /**
     * @var string
     */
    private $cacheDirPath;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var UrlFetcher
     */
    private $fetcher;

    public function __construct(string $projectDir)
    {
        $this->cacheDirPath = "$projectDir/var/snapshots";

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->cacheDirPath);

        $this->fetcher = new UrlFetcher();
    }

    public function clearCache(): void
    {
        $this->fs->remove($this->cacheDirPath);
        $this->fs->mkdir($this->cacheDirPath);
    }

    /**
     * @param string $url
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     */
    public function get(string $url): WebpageSnapshot
    {
        $snapshotPath = $this->snapshotPathForUrl($url);

        if ($this->isCached($snapshotPath)) {
            return $this->getCached($snapshotPath);
        } else {
            $this->putCache($snapshotPath, $result = $this->download($url));

            return $result;
        }
    }

    /**
     * @param string $url
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     * @throws Exception
     */
    private function download(string $url): WebpageSnapshot
    {
        return new WebpageSnapshot($url, $this->fetcher->get($url), new DateTime('now', new DateTimeZone('UTC')));
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
                preg_replace('#^(https?://(www\.)?)?#', '', $url)
            ), '_');
    }

    private function isCached(string $snapshotPath): bool
    {
        return file_exists($snapshotPath);
    }

    private function getCached(string $snapshotPath): WebpageSnapshot
    {
        return WebpageSnapshot::fromFile($snapshotPath);
    }

    private function putCache(string $snapshotPath, WebpageSnapshot $snapshot): void
    {
        $this->fs->mkdir(dirname($snapshotPath));
        $this->fs->dumpFile($snapshotPath, json_encode($snapshot, self::JSON_SERIALIZATION_OPTIONS));
    }
}
