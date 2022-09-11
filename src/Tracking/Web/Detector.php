<?php

declare(strict_types=1);

namespace App\Tracking\Web;

use App\Tracking\Web\WebpageSnapshot\Snapshot;
use TRegx\CleanRegex\Pattern;

use function Psl\Str\Byte\contains_ci;

class Detector
{
    private const WIXSITE_CONTENTS_REGEXP = '<meta\s+name="generator"\s+content="Wix\.com Website Builder"\s*/?>';
    private const FA_JOUNRAL_CONTENTS_NEEDLE_CI = 'journal -- fur affinity [dot] net</title>';
    private const TWITTER_CONTENTS_SEARCH_STRING = 'Twitter</title>';

    private readonly Pattern $wixsiteContentsPattern;

    public function __construct()
    {
        $this->wixsiteContentsPattern = pattern(self::WIXSITE_CONTENTS_REGEXP, 'si');
    }

    public function isWixsite(Snapshot $webpageSnapshot): bool
    {
        if (contains_ci($webpageSnapshot->url, '.wixsite.com/')) {
            return true;
        }

        return $this->wixsiteContentsPattern->test($webpageSnapshot->contents);
    }

    public function isTrello(Snapshot $webpageSnapshot): bool
    {
        return contains_ci($webpageSnapshot->url, '//trello.com/');
    }

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

    public function isTwitter(string $websiteContents): bool
    {
        return contains_ci($websiteContents, self::TWITTER_CONTENTS_SEARCH_STRING);
    }
}
