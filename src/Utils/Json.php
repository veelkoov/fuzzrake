<?php

declare(strict_types=1);

namespace App\Utils;

use JsonException;

abstract class Json
{
    /**
     * @param mixed $input
     *
     * @throws JsonException
     */
    public static function encode($input, int $options = 0): string
    {
        return json_encode($input, $options | JSON_THROW_ON_ERROR);
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public static function decode(string $input)
    {
        return json_decode($input, true, 521, JSON_THROW_ON_ERROR);
    }
}
