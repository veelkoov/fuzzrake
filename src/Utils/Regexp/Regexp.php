<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use TRegx\SafeRegex\Exception\PregException;
use TRegx\SafeRegex\preg;

abstract class Regexp
{
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
            try {
                $result = preg::replace("$patternPrefix$pattern$patternSuffix", $replacement, $result);
            } catch (PregException $e) {
                throw new RuntimeRegexpException(previous: $e);
            }
        }

        return $result;
    }
}
