<?php

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\Regexp\Utils as Regexp;

class Utils
{
    public static function artisanNamesSafeForCli(Artisan ...$artisans)
    {
        $names = $makerIds = [];

        foreach (array_filter($artisans) as $artisan) {
            $names = array_merge($names, $artisan->getAllNamesArr());
            $makerIds = array_merge($makerIds, $artisan->getAllMakerIdsArr());
        }

        return self::strSafeForCli(implode(' / ', array_merge(
            array_filter(array_unique($names)),
            array_filter(array_unique($makerIds))
        )));
    }

    public static function strSafeForCli(string $input): string
    {
        return str_replace(["\r", "\n", '\\'], ['\r', '\n', '\\'], $input);
    }

    public static function undoStrSafeForCli(string $input): string
    {
        return str_replace(['\r', '\n', '\\'], ["\r", "\n", '\\'], $input);
    }

    public static function shortPrintUrl(string $originalUrl): string
    {
        $url = Regexp::replace('#^https?://(www\.)?#', '', $originalUrl);
        $url = Regexp::replace('/\/?(#profile)?$/', '', $url);
        $url = str_replace('/user/', '/u/', $url);
        $url = str_replace('/journal/', '/j/', $url);

        if (strlen($url) > 50) {
            $url = substr($url, 0, 40).'...';
        }

        return $url;
    }

    /**
     * @param $input
     * @param int $options
     *
     * @return string
     *
     * @throws JsonException
     */
    public static function toJson($input, $options = 0): string
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
    public static function fromJson(string $input)
    {
        $result = json_decode($input, true);

        if (JSON_ERROR_NONE !== json_last_error()) { // FIXME: Use 7.3 JSON_THROW_ON_ERROR
            throw new JsonException('Failed to decode data from JSON: '.json_last_error_msg());
        }

        return $result;
    }

    public static function obscureContact(string $input): string
    {
        return implode('@', array_map(function (string $input): string {
            $len = mb_strlen($input);

            if ($len >= 3) {
                $pLen = max(1, (int)($len / 4));
                return mb_substr($input, 0, $pLen).str_repeat('*', $len - 2*$pLen).mb_substr($input, -$pLen);
            } elseif ($len == 2) {
                return mb_substr($input, 0, 1).'*';
            } else {
                return $input;
            }
        }, explode('@', $input)));
    }
}
