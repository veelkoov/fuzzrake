<?php

declare(strict_types=1);

namespace App\Utils;

use App\Data\Definitions\Fields\Field;
use Composer\Pcre\Preg;
use RuntimeException;

/**
 * @phpstan-type Keyable Field|string|int|bool
 */
final class CacheKey
{
    // https://symfony.com/doc/current/cache.html#cache-item-keys-and-values
    private const string DISALLOWED_CHAR_REGEX = '#[^a-zA-Z0-9]#';
    private const string SEPARATOR = '.';
    private const string ORD_WRAP = '_';

    /**
     * @param Keyable[]|Keyable $keyParts
     */
    public static function get(mixed $keyParts): string
    {
        if (!is_array($keyParts)) {
            $keyParts = [$keyParts];
        }

        $keyParts = arr_map($keyParts, function (mixed $item): string {
            if ($item instanceof Field) {
                $result = $item->value;
            } elseif (is_string($item)) {
                $result = $item;
            } elseif (is_int($item)) {
                $result = (string) $item;
            } elseif (is_bool($item)) {
                $result = $item ? 'true' : 'false';
            } else {
                throw new RuntimeException(gettype($item).' is not supported as a cache key part');
            }

            return Preg::replaceCallback(self::DISALLOWED_CHAR_REGEX, self::replaceCallback(...), $result);
        });

        return implode(self::SEPARATOR, $keyParts);
    }

    /**
     * @param array<array-key, ?string> $captures
     */
    private static function replaceCallback(array $captures): string
    {
        return self::ORD_WRAP.ord(Enforce::string($captures[0])).self::ORD_WRAP;
    }
}
