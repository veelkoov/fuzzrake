<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Pattern;

final class CreatorId
{
    use UtilityClass;

    final public const VALID_REGEX = '^([A-Z0-9]{7})?$';

    public static function isValid(string $creatorId): bool
    {
        static $pattern = null;

        $pattern ??= Pattern::of(self::VALID_REGEX);

        return $pattern->test($creatorId);
    }
}
