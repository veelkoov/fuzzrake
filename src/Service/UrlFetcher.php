<?php

namespace App\Service;

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

    const DELAY_FOR_HOST = 5;

    const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.1; +http://getfursu.it/)';

    public function __construct(string $projectDir)
    {
        $this->snapshotsDirPath = "$projectDir/var/snapshots/";

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->snapshotsDirPath);

        $this->lastRequests = [];
    }

    public function fetchWebPage(string $url, bool $useCached): string
    {
        $snapshotPath = $this->snapshotPathForUrl($url);

        if ($useCached && file_exists($snapshotPath)) {
            return file_get_contents($snapshotPath);
        } else {
            $webpageContents = $this->curlFetchUrl($url);
            $this->fs->dumpFile($snapshotPath, $webpageContents);

            return $webpageContents;
        }
    }

    private function curlFetchUrl(string $url): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);

        $this->delayForHost($url);
        $result = curl_exec($ch);
        $this->updateLastHostCall($url);

        if ($result === false) {
            throw new \LogicException("Failed to fetch URL: $url, " . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    private function delayForHost(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (array_key_exists($host, $this->lastRequests)) {
            $this->waitUntil($this->lastRequests[$host], self::DELAY_FOR_HOST);
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
        return $this->snapshotsDirPath . $this->urlToId($url) . '.html';
    }

    private function urlToId(string $url): string
    {
        return preg_replace('#[^a-z0-9_.-]+#i', '_', $url);
    }
}
