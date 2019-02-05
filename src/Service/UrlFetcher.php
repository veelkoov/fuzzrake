<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\WebpageSnapshot;
use App\Utils\WebsiteInfo;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;

class UrlFetcher
{
    /**
     * @var array
     */
    private $lastRequests;
    /**
     * @var string
     */
    private $snapshotsDirPath;

    /**
     * @var Filesystem
     */
    private $fs;

    const DELAY_FOR_HOST_SEC = 5;
    const CONNECTION_TIMEOUT_SEC = 10;

    const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.5; +http://getfursu.it/)';

    public function __construct(string $projectDir)
    {
        $this->snapshotsDirPath = "$projectDir/var/snapshots/";

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->snapshotsDirPath);

        $this->lastRequests = [];
    }

    /**
     * @param string $url
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     */
    public function fetchWebpage(string $url): WebpageSnapshot
    {
        $snapshotPath = $this->snapshotPathForUrl($url);

        $this->downloadIfNotCached($url, $snapshotPath);

        return new WebpageSnapshot($url, file_get_contents($snapshotPath), $this->getFileMTimeUtc($snapshotPath));
    }

    public function clearCache(): void
    {
        $this->fs->remove($this->snapshotsDirPath);
        $this->fs->mkdir($this->snapshotsDirPath);
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @throws UrlFetcherException
     */
    private function curlFetchUrl(string $url): string
    {
        $ch = $this->getCurlSessionHandle($url);

        if (false === $ch) {
            throw new UrlFetcherException('Failed to initialize CURL');
        }

        $this->delayForHost($url);
        $result = curl_exec($ch);
        $this->updateLastHostCall($url);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg = curl_error($ch);
        curl_close($ch);

        if (false === $result || !in_array($httpCode, [200, 401])) {
            throw new UrlFetcherException("Failed to fetch URL ($httpCode): $url, ".($errorMsg ?: 'CURL failed'));
        }

        return $result;
    }

    private function delayForHost(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (array_key_exists($host, $this->lastRequests)) {
            $this->waitUntil($this->lastRequests[$host], self::DELAY_FOR_HOST_SEC);
        }
    }

    private function updateLastHostCall(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        $this->lastRequests[$host] = time();
    }

    private function waitUntil($basetime, $delay): void
    {
        $secondsToWait = $basetime + $delay - time();

        if ($secondsToWait > 0) {
            sleep($secondsToWait);
        }
    }

    private function snapshotPathForUrl(string $url): string
    {
        return $this->snapshotsDirPath.$this->urlToId($url).'.html';
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

    /**
     * @param string $url
     *
     * @return resource
     */
    private function getCurlSessionHandle(string $url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT_SEC);
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'identity');

        if (WebsiteInfo::isFurAffinity($url, null) && !empty($_ENV['FA_COOKIE'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $_ENV['FA_COOKIE']);
        }

        return $ch;
    }

    /**
     * @param string $url
     * @param string $snapshotPath
     *
     * @throws UrlFetcherException
     */
    private function downloadIfNotCached(string $url, string $snapshotPath): void
    {
        if (!file_exists($snapshotPath)) {
            $webpageContents = $this->curlFetchUrl($url);
            $this->fs->dumpFile($snapshotPath, $webpageContents);
        }
    }

    /**
     * @param $filepath
     *
     * @return DateTime
     */
    private function getFileMTimeUtc($filepath): DateTime
    {
        return new DateTime('@'.(string) filemtime($filepath));
    }
}
