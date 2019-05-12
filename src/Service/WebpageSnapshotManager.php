<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Web\UrlFetcher;
use App\Utils\Web\UrlFetcherException;
use App\Utils\Web\WebpageSnapshot;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;

class WebpageSnapshotManager
{
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
        $this->cacheDirPath = "$projectDir/var/snapshots/";

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

        $this->downloadIfNotCached($url, $snapshotPath);

        return new WebpageSnapshot($url, file_get_contents($snapshotPath), $this->getFileMTimeUtc($snapshotPath));
    }

    private function snapshotPathForUrl(string $url): string
    {
        return $this->cacheDirPath.$this->urlToId($url).'.html';
    }

    private function urlToId(string $url): string
    {
        return trim(
                preg_replace('#[^a-z0-9_.-]+#i', '_',
                    preg_replace('#^(https?://(www\.)?)?#', '',
                        preg_replace('#\?.*$#', '', $url)
                    )
                ), '_').'-'.hash('sha512', $url);
    }

    private function downloadIfNotCached(string $url, string $snapshotPath): void
    {
        if (!file_exists($snapshotPath)) {
            $webpageContents = $this->fetcher->get($url);
            $this->fs->dumpFile($snapshotPath, $webpageContents);
        }
    }

    private function getFileMTimeUtc($filepath): DateTime
    {
        return new DateTime('@'.(string) filemtime($filepath));
    }
}
