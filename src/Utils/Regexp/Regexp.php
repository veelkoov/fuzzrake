<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

abstract class Regexp
{
    public static function match(string $pattern, string $subject, array &$matches = null, string $debugInfo = ''): bool
    {
        $result = preg_match($pattern, $subject, $matches);

        if (false === $result) {
            throw new RuntimeRegexpException("Regexp '$pattern' failed ($debugInfo); preg_last_error=".preg_last_error());
        }

        return 1 === $result;
    }

    public static function replace(string $pattern, string $replacement, string $subject, string $debugInfo = ''): string
    {
        $result = preg_replace($pattern, $replacement, $subject);

        if (null === $result) {
            throw new RuntimeRegexpException("Regexp '$pattern' failed ($debugInfo); preg_last_error=".preg_last_error());
        }

        return $result;
    }

    public static function replaceAll(array $replacements, string $subject, string $patternPrefix = '', string $patternSuffix = ''): string
    {
        $result = $subject;

        foreach ($replacements as $pattern => $replacement) {
            $result = self::replace("$patternPrefix$pattern$patternSuffix", $replacement, $result);
        }

        return $result;
    }

    public static function matchAll(string $pattern, string $subject, array &$matches = null, string $debugInfo = ''): int
    {
        $result = preg_match_all($pattern, $subject, $matches);

        if (false === $result) {
            throw new RuntimeRegexpException("Regexp '$pattern' failed ($debugInfo); preg_last_error=".preg_last_error());
        }

        return $result;
    }
}
