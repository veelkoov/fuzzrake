<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Utils\Traits\UtilityClass;
use Composer\Pcre\Preg;

final class CreatorId
{
    use UtilityClass;

    final public const string VALID_REGEX = '#^([A-Z0-9]{7})?$#';

    public static function isValid(string $creatorId): bool
    {
        return Preg::isMatch(self::VALID_REGEX, $creatorId);
    }
}
