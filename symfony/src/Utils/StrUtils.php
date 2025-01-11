<?php

declare(strict_types=1);

namespace App\Utils;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;

final class StrUtils
{
    use UtilityClass;

    public static function artisanNamesSafeForCli(Artisan ...$artisans): string
    {
        $names = $makerIds = [];

        foreach ($artisans as /* @var Artisan $artisan */ $artisan) {
            $names = array_merge($artisan->getAllNames(), $names);
            $makerIds = array_merge($artisan->getAllMakerIds(), $makerIds);
        }

        return self::strSafeForCli(implode(' / ', [...array_filter(array_unique($names)), ...array_filter(array_unique($makerIds))]));
    }

    public static function strSafeForCli(string $input): string
    {
        return str_replace(["\r", "\n", '\\'], ['\r', '\n', '\\'], $input);
    }

    public static function undoStrSafeForCli(string $input): string
    {
        return str_replace(['\r', '\n', '\\'], ["\r", "\n", '\\'], $input);
    }

    public static function ucfirst(string $input): string
    {
        return mb_strtoupper(mb_substr($input, 0, 1)).mb_substr($input, 1);
    }

    /**
     * @param psFieldValue $value
     */
    public static function asStr(mixed $value): string
    {
        if (null === $value) {
            return 'unknown';
        } elseif ($value instanceof DateTimeImmutable) {
            return $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof Ages) {
            return (string) $value->value;
        } elseif ($value instanceof ContactPermit) {
            return (string) $value->value;
        } elseif (is_int($value)) {
            return (string) $value;
        } elseif (is_bool($value)) {
            return $value ? 'True' : 'False';
        } elseif (is_array($value)) {
            return implode(', ', $value);
        } else {
            return $value;
        }
    }
}
