<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use App\Twig\AppExtensions;
use App\Utils\Regexp\Regexp;

abstract class StrUtils
{
    public static function artisanNamesSafeForCli(Artisan ...$artisans): string
    {
        $names = $makerIds = [];

        foreach (array_filter($artisans) as /* @var Artisan $artisan */ $artisan) {
            $names = array_merge($artisan->getAllNamesArr(), $names);
            $makerIds = array_merge($artisan->getAllMakerIdsArr(), $makerIds);
        }

        return self::strSafeForCli(implode(' / ', array_merge(
            array_filter(array_unique($names)),
            array_filter(array_unique($makerIds))
        )));
    }

    public static function strSafeForCli(string $input): string
    {
        return str_replace(["\r", "\n", '\\'], ['\r', '\n', '\\'], $input);
    }

    public static function undoStrSafeForCli(string $input): string
    {
        return str_replace(['\r', '\n', '\\'], ["\r", "\n", '\\'], $input);
    }

    /**
     * @noinspection PhpUnused
     *
     * @see AppExtensions
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

    public static function ucfirst(string $input): string
    {
        return mb_strtoupper(mb_substr($input, 0, 1)).mb_substr($input, 1);
    }
}
