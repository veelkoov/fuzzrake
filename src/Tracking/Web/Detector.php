<?php

declare(strict_types=1);

namespace App\Tracking\Web;

use App\Tracking\Web\WebpageSnapshot\Snapshot;

use function Psl\Str\Byte\contains_ci;

class Detector
{
    private const FA_JOUNRAL_CONTENTS_NEEDLE_CI = 'journal -- fur affinity [dot] net</title>';

    public function isInstagram(Snapshot|string $subject): bool
    {
        if (!is_string($subject)) {
            $subject = $subject->url;
        }

        return contains_ci($subject, 'instagram.com/');
    }

    public function isFurAffinity(string $url): bool
    {
        return contains_ci($url, 'furaffinity.net/');
    }

    public function isNotFurAffinityJournal(string $webpageContents): bool
    {
        return !contains_ci($webpageContents, self::FA_JOUNRAL_CONTENTS_NEEDLE_CI);
    }
}
