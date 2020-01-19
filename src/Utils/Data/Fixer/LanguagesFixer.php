<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;
use App\Utils\StrUtils;

class LanguagesFixer extends StringFixer
{
    private const LANGUAGE_REGEXP = '#(?<prefix>a small bit of |bit of |a little |some |moderate |basic |elementary |slight |limited )?(?<language>.+)(?<suffix> \(limited\))?#i';

    public function fix(string $fieldName, string $subject): string
    {
        $subject = parent::fix($fieldName, $subject);

        $subject = Regexp::split('#[\n,;&]|[, ]and #', $subject);
        $subject = array_filter(array_map('trim', $subject));
        $subject = array_map(function (string $language): string {
            Regexp::match(self::LANGUAGE_REGEXP, $language, $matches);

            $language = $matches['language'];
            $suffix = $matches['prefix'] || ($matches['suffix'] ?? '') ? ' (limited)' : '';

            $language = StrUtils::ucfirst($language);

            return $language.$suffix;
        }, $subject);

        sort($subject);

        return implode("\n", $subject);
    }
}
