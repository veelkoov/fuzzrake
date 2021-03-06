<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

final class Contact
{
    use UtilityClass;

    public const INVALID = 'INVALID';
    public const TWITTER = 'TWITTER';
    public const TELEGRAM = 'TELEGRAM';
    public const E_MAIL = 'E-MAIL';

    private const PATTERNS = [
        '(?:^|email: ?| |\()([a-z0-9._-]+@[a-z0-9.]+)(?:$|[ )])' => [self::E_MAIL,   ''],
        'telegram *[:-]? ?[ @]([a-z0-9_]+)'                      => [self::TELEGRAM, '@'],
        '@?([a-z0-9_]+) (?:on|-) (twitter or )?telegram'         => [self::TELEGRAM, '@'],
        '@?([a-z0-9_]+)( on|@) twitter'                          => [self::TWITTER,  '@'],
        '^https://twitter.com/([a-z0-9_-]+)$'                    => [self::TWITTER,  ''],
        'twitter[-:, ]* ?@?([a-z0-9_]+)'                         => [self::TWITTER,  '@'],
    ];

    /**
     * @return string[]
     */
    public static function parse(string $input): array
    {
        $input = trim($input);

        if ('' === $input || '-' === $input) {
            return ['', ''];
        }

        foreach (self::PATTERNS as $pattern => $template) {
            $result = pattern($pattern, 'i')
                ->match($input)
                ->findFirst(function (Detail $detail) use ($template): array {
                    try {
                        return [$template[0], $template[1].$detail->group(1)];
                    } catch (NonexistentGroupException $e) {
                        throw new UnbelievableRuntimeException($e);
                    }
                })
                ->orReturn(null);

            if (null !== $result) {
                return $result;
            }
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
