<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Regexp\Regexp;

abstract class Contact
{
    public const INVALID = 'INVALID';
    public const TWITTER = 'TWITTER';
    public const TELEGRAM = 'TELEGRAM';
    public const E_MAIL = 'E-MAIL';

    /**
     * @return string[]
     */
    public static function parse(string $input): array
    {
        $input = trim($input);

        if ('' === $input || '-' === $input) {
            return ['', ''];
        }

        if (Regexp::match('#(?:^|email: ?| |\()([a-z0-9._-]+@[a-z0-9.]+)(?:$|[ )])#i', $input, $matches)) {
            return [self::E_MAIL, $matches[1]];
        }

        if (Regexp::match('#telegram *[:-]? ?[ @]([a-z0-9_]+)#i', $input, $matches)) {
            return [self::TELEGRAM, '@'.$matches[1]];
        }

        if (Regexp::match('#@?([a-z0-9_]+) (?:on|-) (twitter or )?telegram#i', $input, $matches)) {
            return [self::TELEGRAM, '@'.$matches[1]];
        }

        if (Regexp::match('#@?([a-z0-9_]+)( on|@) twitter#i', $input, $matches)) {
            return [self::TWITTER, '@'.$matches[1]];
        }

        if (Regexp::match('#^https://twitter.com/([a-z0-9_-]+)$#i', $input, $matches)) {
            return [self::TWITTER, $matches[1]];
        }

        if (Regexp::match('#twitter[-:, ]* ?@?([a-z0-9_]+)#i', $input, $matches)) {
            return [self::TWITTER, '@'.$matches[1]];
        }

        return [self::INVALID, ''];
    }

    public static function obscure(string $input): string
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
