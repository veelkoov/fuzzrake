<?php
declare(strict_types=1);

namespace App\Utils;


class WebsiteInfo
{
    const FA_URL_SEARCH_STRING = 'furaffinity.net/';
    const FA_CONTENTS_SEARCH_STRING = 'fur affinity [dot] net</title>';
    const FA_JOUNRAL_CONTENTS_SEARCH_STRING = 'journal -- fur affinity [dot] net</title>';

    const WIXSITE_CONTENTS_REGEXP = '#<meta\s+name="generator"\s+content="Wix\.com Website Builder"\s*/?>#si';

    const TWITTER_CONTENTS_SEARCH_STRING = '| Twitter</title>';

    public static function isWixsite(WebpageSnapshot $webpageSnapshot): bool
    {
        if (stripos($webpageSnapshot->getUrl(), '.wixsite.com') !== false) {
            return true;
        }

        if (preg_match(self::WIXSITE_CONTENTS_REGEXP, $webpageSnapshot->getContents()) === 1) {
            return true;
        }

        return false;
    }

    public static function isFurAffinity(?string $url, ?string $webpageContents): bool
    {
        if ($url !== null) {
            return stripos($url, self::FA_URL_SEARCH_STRING) !== false;
        }

        if ($webpageContents !== null) {
            return stripos($webpageContents, self::FA_CONTENTS_SEARCH_STRING) !== false;
        }

        return false;
    }

    public static function isFurAffinityUserProfile(?string $url, ?string $webpageContents): bool
    {
        if (!self::isFurAffinity($url, $webpageContents)) {
            return false;
        }

        return stripos($webpageContents, self::FA_JOUNRAL_CONTENTS_SEARCH_STRING) === false;
    }

    public static function isTwitter(string $websiteContents): bool
    {
        return stripos($websiteContents, self::TWITTER_CONTENTS_SEARCH_STRING) !== false;
    }
}