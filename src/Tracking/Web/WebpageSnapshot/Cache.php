<?php

declare(strict_types=1);

namespace App\Tracking\Web\WebpageSnapshot;

use App\Tracking\Web\Url\Fetchable;
use App\Tracking\Web\Url\UrlUtils;
use App\Utils\DateTime\DateTimeException;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\IOException;

use function Psl\Str\strip_prefix;
use function Psl\Str\uppercase;

class Cache
{
    private readonly string $cacheDirPath;

    public function __construct(
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%/var/snapshots')]
        string $cacheDirPath,
    ) {
        $this->cacheDirPath = $cacheDirPath;
    }

    public function has(Fetchable $url): bool
    {
        return is_dir($this->getBaseDir($url->getUrl()));
    }

    public function get(Fetchable $url): ?Snapshot
    {
        if (!$this->has($url)) {
            return null;
        }

        try {
            return Jar::load($this->getBaseDir($url->getUrl()));
        } catch (JsonException|DateTimeException|InvalidArgumentException $e) {
            $this->logger->warning('Failed reading snapshot from cache', ['url' => $url, 'exception' => $e]);

            return null;
        }
    }

    public function set(Fetchable $url, Snapshot $snapshot): void
    {
        try {
            Jar::dump($this->getBaseDir($url->getUrl()), $snapshot);
        } catch (JsonException|IOException $e) {
            $this->logger->warning('Failed saving snapshot into cache', ['url' => $url, 'exception' => $e]);
        }
    }

    private function getBaseDir(string $url): string
    {
        $hostName = strip_prefix(UrlUtils::hostFromUrl($url), 'www.');

        $urlFsSafe = UrlUtils::safeFileNameFromUrl($url);
        $urlFsSafe = strip_prefix($urlFsSafe, $hostName);
        $urlFsSafe = ltrim($urlFsSafe, '_');

        $firstLetter = uppercase(($hostName.'_')[0]);
        $optionalDash = '' === $urlFsSafe ? '' : '-';
        $urlHash = hash('sha224', $url);

        return "{$this->cacheDirPath}/{$firstLetter}/{$hostName}/{$urlFsSafe}{$optionalDash}{$urlHash}";
    }
}
