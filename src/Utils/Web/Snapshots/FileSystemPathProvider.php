<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use App\Utils\Web\Url\Url;
use Composer\Pcre\Preg;

class FileSystemPathProvider
{
    public function getSnapshotDirPath(Url $url): string
    {
        $hostName = str_strip_prefix($url->getHost(), 'www.');

        $urlFsSafe = ltrim(str_strip_prefix($this->toFileSystemSafeString($url->getUrl()), $hostName), '_');

        $firstLetter = mb_strtoupper("{$hostName}_")[0];
        $optionalDash = '' === $urlFsSafe ? '' : '-';
        $urlHash = hash('sha224', $url->getUrl());

        return "$firstLetter/$hostName/$urlFsSafe$optionalDash$urlHash";
    }

    private function toFileSystemSafeString(string $url): string
    {
        $url = Preg::replace('~^https?://(www\.)?|[?#].+$~i', '', $url);
        $url = Preg::replace('~[^a-z0-9_.-]+~i', '_', $url);

        return trim($url, '_');
    }
}
