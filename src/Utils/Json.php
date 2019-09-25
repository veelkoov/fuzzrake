<?php

declare(strict_types=1);

namespace App\Utils;

class Json
{
    private function __construct()
    {
    }

    /**
     * @param $input
     * @param int $options
     *
     * @return string
     *
     * @throws JsonException
     */
    public static function encode($input, $options = 0): string
    {
        $result = json_encode($input, $options);

        if (JSON_ERROR_NONE !== json_last_error()) { // FIXME: Use 7.3 JSON_THROW_ON_ERROR
            throw new JsonException('Failed to encode data to JSON: '.json_last_error_msg());
        }

        return $result;
    }

    /**
     * @param string $input
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public static function decode(string $input)
    {
        $result = json_decode($input, true);

        if (JSON_ERROR_NONE !== json_last_error()) { // FIXME: Use 7.3 JSON_THROW_ON_ERROR
            throw new JsonException('Failed to decode data from JSON: '.json_last_error_msg());
        }

        return $result;
    }
}
