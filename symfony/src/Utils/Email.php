<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Pattern;

final class Email
{
    use UtilityClass;

    // Pattern taken from the Symfony's EmailValidator
    // @author Bernhard Schussek <bschussek@gmail.com>
    private const string PATTERN_HTML5_ALLOW_NO_TLD = '^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$';

    private static ?Pattern $pattern = null;

    public static function obfuscate(string $input): string
    {
        return implode('@', array_map(function (string $input): string {
            $len = mb_strlen($input);

            if ($len >= 3) {
                $pLen = max(1, (int) ($len / 4));

                return mb_substr($input, 0, $pLen).str_repeat('*', $len - 2 * $pLen).mb_substr($input, -$pLen);
            } elseif (2 == $len) {
                return mb_substr($input, 0, 1).'*';
            } else {
                return $input;
            }
        }, explode('@', $input)));
    }

    public static function isValid(string $email): bool
    {
        self::$pattern ??= Pattern::of(self::PATTERN_HTML5_ALLOW_NO_TLD);

        return self::$pattern->test($email);
    }
}
