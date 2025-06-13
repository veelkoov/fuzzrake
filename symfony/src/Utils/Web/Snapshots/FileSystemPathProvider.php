<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use App\Utils\Web\Url\Url;
use Psl\Str;
use TRegx\CleanRegex\Pattern;

class FileSystemPathProvider
{
    private readonly Pattern $urlPrefixAndSuffixRegex;
    private readonly Pattern $fsUnfriendlyCharactersRegex;

    public function __construct()
    {
        $this->urlPrefixAndSuffixRegex = Pattern::of('^https?://(www\.)?|[?#].+$', 'i');
        $this->fsUnfriendlyCharactersRegex = Pattern::of('[^a-z0-9_.-]+', 'i');
    }

    public function getSnapshotDirPath(Url $url): string
    {
        $hostName = Str\strip_prefix($url->getHost(), 'www.');

        $urlFsSafe = ltrim(Str\strip_prefix($this->toFileSystemSafeString($url->getUrl()), $hostName), '_');

        $firstLetter = mb_strtoupper("{$hostName}_")[0];
        $optionalDash = '' === $urlFsSafe ? '' : '-';
        $urlHash = hash('sha224', $url->getUrl());

        return "$firstLetter/$hostName/$urlFsSafe$optionalDash$urlHash";
    }

    private function toFileSystemSafeString(string $url): string
    {
        $url = $this->urlPrefixAndSuffixRegex->prune($url);
        $url = $this->fsUnfriendlyCharactersRegex->replace($url)->with('_');

        return trim($url, '_');
    }
}
