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
    const INSTAGRAM_CONTENTS_REGEXP = '#Instagram photos and videos\s*</title>#si';

    public static function isWixsite(WebpageSnapshot $webpageSnapshot): bool
    {
        if (false !== stripos($webpageSnapshot->getUrl(), '.wixsite.com/')) {
            return true;
        }

        if (1 === preg_match(self::WIXSITE_CONTENTS_REGEXP, $webpageSnapshot->getContents())) {
            return true;
        }

        return false;
    }

    public static function isTrello(WebpageSnapshot $webpageSnapshot): bool
    {
        return false !== stripos($webpageSnapshot->getUrl(), '//trello.com/');
    }

    public static function isFurAffinity(?string $url, ?string $webpageContents): bool
    {
        if (null !== $url) {
            return false !== stripos($url, self::FA_URL_SEARCH_STRING);
        }

        if (null !== $webpageContents) {
            return false !== stripos($webpageContents, self::FA_CONTENTS_SEARCH_STRING);
        }

        return false;
    }

    public static function isFurAffinityUserProfile(?string $url, ?string $webpageContents): bool
    {
        if (!self::isFurAffinity($url, $webpageContents)) {
            return false;
        }

        return false === stripos($webpageContents, self::FA_JOUNRAL_CONTENTS_SEARCH_STRING);
    }

    public static function isTwitter(string $websiteContents): bool
    {
        return false !== stripos($websiteContents, self::TWITTER_CONTENTS_SEARCH_STRING);
    }

    public static function isInstagram(string $webpageContents): bool
    {
        return 1 === preg_match(self::INSTAGRAM_CONTENTS_REGEXP, $webpageContents);
    }
}
