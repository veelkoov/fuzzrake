<?php

declare(strict_types=1);

namespace App\Utils;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;

final class StrUtils
{
    use UtilityClass;

    public static function creatorNamesSafeForCli(Creator ...$creators): string
    {
        $names = $creatorIds = [];

        foreach ($creators as $creator) {
            $names = array_merge($creator->getAllNames(), $names);
            $creatorIds = array_merge($creator->getAllCreatorIds(), $creatorIds);
        }

        return self::strSafeForCli(implode(' / ', [...array_filter(array_unique($names)), ...array_filter(array_unique($creatorIds))]));
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
