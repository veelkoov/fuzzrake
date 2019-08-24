<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

class Utils
{
    private function __construct()
    {
    }

    /**
     * @param string     $pattern
     * @param string     $subject
     * @param array|null $matches
     * @param string     $debugInfo
     *
     * @return bool
     */
    public static function match(string $pattern, string $subject, array &$matches = null, string $debugInfo = ''): bool
    {
        $result = preg_match($pattern, $subject, $matches);

        if (false === $result) {
            throw new RuntimeRegexpException("Regexp '$pattern' failed ($debugInfo); preg_last_error=".preg_last_error());
        }

        return 1 === $result;
    }

    /**
     * @param string $pattern
     * @param string $replacement
     * @param string $subject
     * @param string $debugInfo
     *
     * @return string
     */
    public static function replace(string $pattern, string $replacement, string $subject, string $debugInfo = ''): string
    {
        $result = preg_replace($pattern, $replacement, $subject);

        if (null === $result) {
            throw new RuntimeRegexpException("Regexp '$pattern' failed ($debugInfo); preg_last_error=".preg_last_error());
        }

        return $result;
    }

    /**
     * @param string     $pattern
     * @param string     $subject
     * @param array|null $matches
     * @param string     $debugInfo
     *
     * @return int
     */
    public static function matchAll(string $pattern, string $subject, array &$matches = null, string $debugInfo = ''): int
    {
        $result = preg_match_all($pattern, $subject, $matches);

        if (false === $result) {
            throw new RuntimeRegexpException("Regexp '$pattern' failed ($debugInfo); preg_last_error=".preg_last_error());
        }

        return $result;
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @param string $debugInfo
     *
     * @return array
     */
    public static function split(string $pattern, string $subject, string $debugInfo = ''): array
    {
        $result = preg_split($pattern, $subject);

        if (false === $result) {
            throw new RuntimeRegexpException("Regexp '$pattern' failed ($debugInfo); preg_last_error=".preg_last_error());
        }

        return $result;
    }
}
