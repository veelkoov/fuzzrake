<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\DateTime\DateTimeException;
use App\Utils\Json;
use App\Utils\Regexp\Regexp;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class WebpageSnapshotCache
{
    private const JSON_SERIALIZATION_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                                             | JSON_UNESCAPED_LINE_TERMINATORS | JSON_PRETTY_PRINT;
    private const PATH_META_FILE = 'meta_file';
    private const PATH_DATA_FILE = 'data_file';

    private LoggerInterface $logger;
    private string $cacheDirPath;
    private Filesystem $fs;

    public function __construct(LoggerInterface $logger, string $snapshotCacheDirPath)
    {
        $this->logger = $logger;
        $this->cacheDirPath = $snapshotCacheDirPath;

        $this->fs = new Filesystem();
        $this->fs->mkdir($this->cacheDirPath);
    }

    public function has(Fetchable $url): bool
    {
        return file_exists($this->getPaths($this->getBaseDir($url->getUrl()))[self::PATH_META_FILE]);
    }

    public function getOK(Fetchable $url): ?WebpageSnapshot
    {
        $result = $this->get($url);

        return $result && $result->isOK() ? $result : null;
    }

    public function get(Fetchable $url): ?WebpageSnapshot
    {
        if (!$this->has($url)) {
            return null;
        }

        try {
            return $this->load($this->getBaseDir($url->getUrl()));
        } catch (JsonException | DateTimeException | InvalidArgumentException $e) {
            $this->logger->warning('Failed reading snapshot from cache', ['url' => $url, 'exception' => $e]);

            return null;
        }
    }

    public function set(Fetchable $url, WebpageSnapshot $snapshot): void
    {
        try {
            $this->dump($this->getBaseDir($url->getUrl()), $snapshot);
        } catch (JsonException | IOException $e) {
            $this->logger->warning('Failed saving snapshot into cache', ['url' => $url, 'exception' => $e]);
        }
    }

    /**
     * @throws JsonException
     * @throws DateTimeException
     * @throws InvalidArgumentException
     */
    private function load(string $baseDir): WebpageSnapshot
    {
        $paths = $this->getPaths($baseDir);

        $data = Json::decode(file_get_contents($paths[self::PATH_META_FILE]));
        $data['contents'] = file_get_contents($paths[self::PATH_DATA_FILE]);

        $result = WebpageSnapshot::fromArray($data);

        for ($index = 0; $index < $data['childCount']; ++$index) {
            $this->load($this->getChildDirPath($baseDir, $index));
        }

        return $result;
    }

    /**
     * @throws JsonException
     */
    private function dump(string $baseDir, WebpageSnapshot $snapshot): void
    {
        $this->fs->mkdir($baseDir);

        $paths = $this->getPaths($baseDir);
        $this->fs->dumpFile($paths[self::PATH_META_FILE], Json::encode($snapshot->getMetadata(), self::JSON_SERIALIZATION_OPTIONS));
        $this->fs->dumpFile($paths[self::PATH_DATA_FILE], $snapshot->getContents());

        foreach ($snapshot->getChildren() as $index => $child) {
            $this->dump($this->getChildDirPath($baseDir, $index), $child);
        }
    }

    private function getPaths(string $baseDir): array
    {
        return [
            self::PATH_META_FILE => $baseDir.'/metadata.json',
            self::PATH_DATA_FILE => $baseDir.'/contents.data',
        ];
    }

    private function getChildDirPath(string $baseDir, int $childIndex): string
    {
        return "$baseDir/child.$childIndex";
    }

    private function getBaseDir(string $url)
    {
        $hostName = Regexp::replace('#^www\.#', '', UrlUtils::hostFromUrl($url));

        $urlFsSafe = UrlUtils::safeFileNameFromUrl($url);
        if (0 === strpos($urlFsSafe, $hostName)) {
            $urlFsSafe = substr($urlFsSafe, strlen($hostName));
        }

        $urlHash = hash('sha224', $url);

        return "{$this->cacheDirPath}/{$hostName}/{$urlFsSafe}-{$urlHash}";
    }
}
