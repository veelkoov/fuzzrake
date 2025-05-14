<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;
use JsonException;

final class Json
{
    use UtilityClass;

    /**
     * @throws JsonException
     */
    public static function encode(mixed $input, int $options = 0): string
    {
        return json_encode($input, $options | JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function decode(string $input): mixed
    {
        return json_decode($input, true, flags: JSON_THROW_ON_ERROR);
    }
}
