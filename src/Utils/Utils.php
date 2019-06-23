<?php

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\Regexp\RegexpFailure;
use App\Utils\Regexp\Utils as Regexp;

class Utils
{
    public static function artisanNamesSafe(Artisan ...$artisans)
    {
        $names = $makerIds = [];

        foreach (array_filter($artisans) as $artisan) {
            $names = array_merge($names, $artisan->getAllNamesArr());
            $makerIds = array_merge($makerIds, $artisan->getAllMakerIdsArr());
        }

        return self::safeStr(implode(' / ', array_merge(
            array_filter(array_unique($names)),
            array_filter(array_unique($makerIds))
        )));
    }

    public static function safeStr(string $input): string
    {
        return str_replace(["\r", "\n", '\\'], ['\r', '\n', '\\'], $input);
    }

    public static function unsafeStr(string $input): string
    {
        return str_replace(['\r', '\n', '\\'], ["\r", "\n", '\\'], $input);
    }

    /**
     * @param string $originalUrl
     *
     * @return string
     *
     * @throws RegexpFailure
     */
    public static function shortPrintUrl(string $originalUrl): string
    {
        $url = Regexp::replace('#^https?://(www\.)?#', '', $originalUrl);
        $url = Regexp::replace('/\/?(#profile)?$/', '', $url);
        $url = str_replace('/user/', '/u/', $url);
        $url = str_replace('/journal/', '/j/', $url);

        if (strlen($url) > 50) {
            $url = substr($url, 0, 40).'...';
        }

        return $url;
    }
}
