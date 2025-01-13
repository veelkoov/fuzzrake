<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Pattern;

final class CreatorId
{
    use UtilityClass;

    final public const string VALID_REGEX = '^([A-Z0-9]{7})?$';

    private static ?Pattern $pattern = null;

    public static function isValid(string $creatorId): bool
    {
        self::$pattern ??= Pattern::of(self::VALID_REGEX);

        return self::$pattern->test($creatorId);
    }
}
