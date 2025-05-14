<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Regexp\Patterns;
use App\Utils\Traits\UtilityClass;

final class PackedStringList
{
    use UtilityClass;

    private const string STD_SEPARATOR = "\n";

    /**
     * @return list<string>
     */
    public static function unpack(?string $input): array
    {
        if (null === $input || '' === $input) {
            return [];
        }

        return explode(self::STD_SEPARATOR, $input);
    }

    /**
     * @param string[] $input
     */
    public static function pack(array $input): string
    {
        return implode(self::STD_SEPARATOR, $input);
    }

    /**
     * @param string[] $nonsplittables
     *
     * @return string[]
     */
    public static function split(string $input, string $separatorRegexp, array $nonsplittables = []): array
    {
        $nonsplittables = array_fill_keys($nonsplittables, '');
        $i = 0;

        foreach ($nonsplittables as &$uItem) {
            $uItem = 'NONSPLITTABLE'.($i++).'NONSPLITTABLE';
        }

        $input = self::replaceNonsplittables($input, $nonsplittables);

        $result = Patterns::get($separatorRegexp)->split($input);

        $nonsplittables = array_flip($nonsplittables);

        foreach ($result as &$sItem) {
            $sItem = self::replaceNonsplittables($sItem, $nonsplittables);
        }

        return $result;
    }

    /**
     * @param string[] $nonsplittables
     */
    private static function replaceNonsplittables(string $input, array $nonsplittables): string
    {
        return str_replace(array_keys($nonsplittables), array_values($nonsplittables), $input);
    }
}
