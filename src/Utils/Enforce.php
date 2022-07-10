<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

final class Enforce
{
    use UtilityClass;

    public static function string(mixed $input): string
    {
        if (!is_string($input)) {
            throw new InvalidArgumentException('Expected string');
        }

        return $input;
    }
}
