<?php

declare(strict_types=1);

namespace App\Utils;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;
use Veelkoov\Debris\Sets\StringSet;

final class StrUtils
{
    use UtilityClass;

    public static function creatorNamesSafeForCli(Creator ...$creators): string
    {
        $namesAndCreatorIds = new StringSet();

        foreach ($creators as $creator) {
            $namesAndCreatorIds->addAll($creator->getAllNames())->addAll($creator->getAllCreatorIds());
        }

        return $namesAndCreatorIds->filter(static fn (string $item) => '' !== $item)->join(' / ');
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
     * @param psPhpFieldValue $value
     */
    public static function asStr(mixed $value): string
    {
        if (null === $value) {
            return 'unknown';
        } elseif ($value instanceof DateTimeImmutable) {
            return $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof Ages) {
            return $value->value;
        } elseif ($value instanceof ContactPermit) {
            return $value->value;
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
