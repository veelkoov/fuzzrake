<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use JsonException;

use function Psl\File\read;

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

    /**
     * @throws JsonException
     */
    public static function readFile(string $filePath): mixed
    {
        if ('' === $filePath) {
            throw new InvalidArgumentException('File path is required');
        }

        return self::decode(read($filePath));
    }
}
