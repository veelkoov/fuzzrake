<?php
declare(strict_types=1);

namespace App\Utils;


class WebsiteInfo
{
    public static function isWixsite(string $url, string $websiteContents): bool
    {
        if (stripos($url, '.wixsite.com') !== false) {
            return true;
        }

        if (preg_match('#<meta\s+name="generator"\s+content="Wix\.com Website Builder"\s*/?>#si', $websiteContents) === 1) {
            return true;
        }

        return false;
    }

    public static function isFurAffinity(?string $url, ?string $webpageContents): bool
    {
        if ($url !== null) {
            return stripos($url, 'furaffinity.net/') !== false;
        }

        if ($webpageContents !== null) {
            return stripos($webpageContents, 'fur affinity [dot] net</title>') !== false;
        }

        return false;
    }

    public static function isFurAffinityUserProfile(?string $url, ?string $webpageContents): bool
    {
        if (!self::isFurAffinity($url, $webpageContents)) {
            return false;
        }

        return stripos($webpageContents, 'journal -- fur affinity [dot] net</title>') === false;
    }

    public static function isTwitter(string $websiteContents): bool
    {
        return stripos($websiteContents, '| Twitter</title>') !== false;
    }
}