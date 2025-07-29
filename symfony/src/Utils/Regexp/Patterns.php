<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\Traits\UtilityClass;
use Deprecated;
use TRegx\CleanRegex\Pattern;

final class Patterns
{
    use UtilityClass;

    /**
     * @var array<string, Pattern>
     */
    private static array $cache = [];

    /** @deprecated */
    public static function get(string $pattern): Pattern
    {
        return self::$cache[$pattern] ??= Pattern::of($pattern);
    }
}
