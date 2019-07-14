<?php

declare(strict_types=1);

namespace App\Utils;

class StrContextUtils
{
    public const STR_REPRESENTATION_SEPARATOR = "\n";

    private function __construct()
    {
    }

    public static function toStr(StrContextInterface $strContext): string
    {
        return $strContext->getBefore()
            .self::STR_REPRESENTATION_SEPARATOR.$strContext->getSubject()
            .self::STR_REPRESENTATION_SEPARATOR.$strContext->getAfter();
    }

    public static function extractFrom(string $input, string $match, int $contextLength): StrContextInterface
    {
        $index = mb_strpos($input, $match);
        $beforeIndex = max(0, $index - $contextLength);

        return new StrContext(
            mb_substr($input, $beforeIndex, 0 === $beforeIndex ? $index : $contextLength),
            $match,
            mb_substr($input, $index + strlen($match), $contextLength));
    }

    public static function fromString(?string $input): StrContextInterface
    {
        if (null === $input || '' === $input || $input === self::STR_REPRESENTATION_SEPARATOR.self::STR_REPRESENTATION_SEPARATOR) {
            return NullStrContext::get();
        }

        $parts = explode(self::STR_REPRESENTATION_SEPARATOR, $input);

        if (3 !== count($parts)) {
            throw new StrContextRuntimeException('Invalid input: '.Utils::safeStr($input));
        }

        return new StrContext(...$parts);
    }
}
