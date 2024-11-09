<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;

final class Email
{
    use UtilityClass;

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
}
