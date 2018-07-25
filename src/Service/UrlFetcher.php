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

    const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.2; +http://getfursu.it/)';

    public function __construct(string $projectDir)
    {
        $this->snapshotsDirPath = "$projectDir/var/snapshots/";

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->snapshotsDirPath);

        $this->lastRequests = [];
    }

    public function fetchWebPage(string $url): string
    {
        $snapshotPath = $this->snapshotPathForUrl($url);

        if (file_exists($snapshotPath)) {
            $webpageContents = file_get_contents($snapshotPath);
        } else {
            $webpageContents = $this->curlFetchUrl($url);
            $this->fs->dumpFile($snapshotPath, $webpageContents);
        }

        if ($this->isWixsite($url, $webpageContents)) {
            return $this->retrieveWixsiteContents($webpageContents);
        } else {
            return $webpageContents;
        }
    }

    public function clearCache(): void
    {
        $this->fs->remove($this->snapshotsDirPath);
        $this->fs->mkdir($this->snapshotsDirPath);
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

        // TODO: Detect 301/302
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

    private function retrieveWixsiteContents(string $webpageContents): string
    {
        preg_match('#"masterPageJsonFileName"\s*:\s*"(?<hash>[a-z0-9_]+).json"#s', $webpageContents, $matches);

        $hash = $matches['hash'];

        preg_match("#<link[^>]* href=\"(?<data_url>https://static.wixstatic.com/sites/(?!$hash)[a-z0-9_]+\.json\.z\?v=\d+)\"[^>]*>#si",
            $webpageContents, $matches);

        return $this->fetchWebPage($matches['data_url']);
    }

    private function isWixsite(string $url, string $contents): bool
    {
        if (stripos($url, '.wixsite.com') !== false) {
            return true;
        }

        if (preg_match('#<meta\s+name="generator"\s+content="Wix\.com Website Builder"\s*/?>#si', $contents) === 1) {
            return true;
        }

        return false;
    }
}
