<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Utils\Regexp\Patterns;
use App\Utils\Traits\UtilityClass;

final class CreatorId
{
    use UtilityClass;

    final public const string VALID_REGEX = '^([A-Z0-9]{7})?$';

    public static function isValid(string $creatorId): bool
    {
        return Patterns::get(self::VALID_REGEX)->test($creatorId);
    }
}
