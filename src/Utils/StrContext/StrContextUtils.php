<?php

declare(strict_types=1);

namespace App\Utils\StrContext;

use App\Utils\Json;
use App\Utils\Traits\UtilityClass;
use JsonException;

final class StrContextUtils
{
    use UtilityClass;

    public static function extractFrom(string $input, string $match, int $contextLength): StrContextInterface
    {
        $index = mb_strpos($input, $match);
        $beforeIndex = max(0, $index - $contextLength);

        return new StrContext(
            mb_substr($input, $beforeIndex, 0 === $beforeIndex ? $index : $contextLength),
            $match,
            mb_substr($input, $index + mb_strlen($match), $contextLength));
    }

    public static function toStr(?StrContextInterface $strContext): string
    {
        if (null === $strContext || $strContext->empty()) {
            return '';
        }

        try {
            return Json::encode([
                $strContext->getBefore(),
                $strContext->getSubject(),
                $strContext->getAfter(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS);
        } catch (JsonException $e) {
            throw new StrContextRuntimeException($e);
        }
    }

    public static function fromString(?string $input): StrContextInterface
    {
        if (null === $input || '' === $input) {
            return NullStrContext::get();
        }

        try {
            $array = Json::decode($input);
        } catch (JsonException $e) {
            throw new StrContextRuntimeException('Failed to read JSON object', 0, $e);
        }

        self::validateStorageArray($array);

        if ('' === implode('', $array)) {
            return NullStrContext::get();
        }

        return new StrContext(...$array);
    }

    private static function validateStorageArray($array): void
    {
        if (!is_array($array) || 3 != count($array)) {
            throw new StrContextRuntimeException('Invalid JSON object to read from');
        }

        for ($i = 0; $i < 3; ++$i) {
            if (!array_key_exists($i, $array) || !is_string($array[$i])) {
                throw new StrContextRuntimeException('Invalid JSON object to read from');
            }
        }
    }
}
