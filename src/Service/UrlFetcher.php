<?php
declare(strict_types=1);

namespace App\Service;

use App\Utils\WebsiteInfo;
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

    const USER_AGENT = 'Mozilla/5.0 (compatible; GetFursuitBot/0.3; +http://getfursu.it/)';

    public function __construct(string $projectDir)
    {
        $this->snapshotsDirPath = "$projectDir/var/snapshots/";

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->snapshotsDirPath);

        $this->lastRequests = [];
    }

    /**
     * @param string $url
     * @return string
     * @throws UrlFetcherException
     */
    public function fetchWebPage(string $url): string
    {
        $snapshotPath = $this->snapshotPathForUrl($url);

        if (file_exists($snapshotPath)) {
            $webpageContents = file_get_contents($snapshotPath);
        } else {
            $webpageContents = $this->curlFetchUrl($url);
            $this->fs->dumpFile($snapshotPath, $webpageContents);
        }

        return $webpageContents;
    }

    public function clearCache(): void
    {
        $this->fs->remove($this->snapshotsDirPath);
        $this->fs->mkdir($this->snapshotsDirPath);
    }

    /**
     * @param string $url
     * @return string
     * @throws UrlFetcherException
     */
    private function curlFetchUrl(string $url): string
    {
        $ch = $this->getCurlSessionHandle($url);

        $this->delayForHost($url);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->updateLastHostCall($url);

        curl_close($ch);

        if ($result === false) {
            throw new UrlFetcherException("Failed to fetch URL: $url, " . (is_resource($ch) ? curl_error($ch) : 'CURL failed'));
        }

        if ($httpCode !== 200) {
            throw new UrlFetcherException("Got HTTP code $httpCode for URL: $url");
        }

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
        return trim(
            preg_replace('#[^a-z0-9_.-]+#i', '_',
                preg_replace('#^(https?://(www\.)?)?#', '', $url)),
            '_');
    }

    /**
     * @param string $url
     * @return resource
     */
    private function getCurlSessionHandle(string $url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);

        if (WebsiteInfo::isFurAffinity($url, null) && !empty($_ENV['FA_COOKIE'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $_ENV['FA_COOKIE']);
        }

        return $ch;
    }
}
