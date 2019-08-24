<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Regexp\Utils;

class ContactParser
{
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

        if ('' === $input) {
            return ['', ''];
        }

        if (Utils::match('#(?:^|email: ?| |\()([a-z0-9._]+@[a-z0-9.]+)(?:$|[ )])#i', $input, $matches)) {
            return ['E-MAIL', $matches[1]];
        }

        if (Utils::match('#telegram *[:-]? ?[ @]([a-z0-9_]+)#i', $input, $matches)) {
            return ['TELEGRAM', '@'.$matches[1]];
        }

        if (Utils::match('#@?([a-z0-9_]+) (?:on|-) (twitter or )?telegram#i', $input, $matches)) {
            return ['TELEGRAM', '@'.$matches[1]];
        }

        if (Utils::match('#@?([a-z0-9_]+)( on|@) twitter#i', $input, $matches)) {
            return ['TWITTER', '@'.$matches[1]];
        }

        if (Utils::match('#^https://twitter.com/[a-z0-9_-]+$#i', $input, $matches)) {
            return ['TWITTER', $matches[0]];
        }

        if (Utils::match('#twitter[-:, ]* ?@?([a-z0-9_]+)#i', $input, $matches)) {
            return ['TWITTER', '@'.$matches[1]];
        }

        if (Utils::match('#^@[a-z0-9_]+$#i', $input, $matches)) {
            return ['TELEGRAM_OR_TWITTER', $matches[0]];
        }

        return ['UNKNOWN', $input];
    }
}
