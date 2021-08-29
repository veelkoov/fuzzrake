<?php

declare(strict_types=1);

namespace App\Utils;

use App\DataDefinitions\Fields;
use App\Twig\AppExtensions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Traits\UtilityClass;
use DateTimeInterface;

final class StrUtils
{
    use UtilityClass;

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
        $url = pattern('^https?://(www\.)?')->prune($originalUrl);
        $url = pattern('/?(#profile)?$')->prune($url);
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

    public static function fixNewlines(Artisan $artisan): void
    {
        foreach (Fields::persisted() as $field) {
            if (($value = $artisan->get($field)) && is_string($value)) {
                $artisan->set($field, str_replace("\r\n", "\n", $value));
            }
        }
    }

    public static function asStr(DateTimeInterface | bool | string | null $value): string
    {
        if (null === $value) {
            return 'unknown';
        } elseif ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        } elseif (is_bool($value)) {
            $value = $value ? 'True' : 'False';
        }

        return $value;
    }
}
