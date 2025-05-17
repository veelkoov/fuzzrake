<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use Nette\Http\Url;
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

    public function getSnapshotDirPath(string $snapshotUrl): string
    {
        $url = new Url($snapshotUrl);

        $hostName = Str\strip_prefix($url->host, 'www.');
        $firstLetter = mb_strtoupper($hostName[0]);

        $urlFsSafe = ltrim(Str\strip_prefix($this->toFileSystemSafeString($snapshotUrl), $hostName), '_');

        $optionalDash = '' === $urlFsSafe ? '' : '-';
        $urlHash = hash('sha224', $snapshotUrl);

        return "$firstLetter/$hostName/$urlFsSafe$optionalDash$urlHash";
    }

    private function toFileSystemSafeString(string $url): string
    {
        $url = $this->urlPrefixAndSuffixRegex->prune($url);
        $url = $this->fsUnfriendlyCharactersRegex->replace($url)->with('_');

        return trim($url, '_');
    }
}
