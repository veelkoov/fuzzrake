<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Regexp\Utils;

class ContactParser
{
    public const INVALID = 'INVALID';
    public const TWITTER = 'TWITTER';
    public const TELEGRAM = 'TELEGRAM';
    public const E_MAIL = 'E-MAIL';

    public const ALLOW_FEEDBACK = 'FEEDBACK';
    public const ALLOW_ANNOUNCEMENTS = 'ANNOUNCEMENTS';
    public const ALLOW_NOTHING = 'NO';

    private function __construct()
    {
    }

    /**
     * @param string $input
     *
     * @return string[]
     */
    public static function parse(string $input): array
    {
        $input = trim($input);

        if ('' === $input || '-' === $input) {
            return ['', ''];
        }

        if (Utils::match('#(?:^|email: ?| |\()([a-z0-9._]+@[a-z0-9.]+)(?:$|[ )])#i', $input, $matches)) {
            return [self::E_MAIL, $matches[1]];
        }

        if (Utils::match('#telegram *[:-]? ?[ @]([a-z0-9_]+)#i', $input, $matches)) {
            return [self::TELEGRAM, '@'.$matches[1]];
        }

        if (Utils::match('#@?([a-z0-9_]+) (?:on|-) (twitter or )?telegram#i', $input, $matches)) {
            return [self::TELEGRAM, '@'.$matches[1]];
        }

        if (Utils::match('#@?([a-z0-9_]+)( on|@) twitter#i', $input, $matches)) {
            return [self::TWITTER, '@'.$matches[1]];
        }

        if (Utils::match('#^https://twitter.com/([a-z0-9_-]+)$#i', $input, $matches)) {
            return [self::TWITTER, $matches[1]];
        }

        if (Utils::match('#twitter[-:, ]* ?@?([a-z0-9_]+)#i', $input, $matches)) {
            return [self::TWITTER, '@'.$matches[1]];
        }

        return [self::INVALID, ''];
    }
}
