<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;
use App\Utils\StrUtils;

class LanguagesFixer extends StringFixer
{
    private string $regexp;

    /**
     * @var string[]
     */
    private array $replacements;

    public function __construct(array $languages, array $strings)
    {
        parent::__construct($strings);

        $this->regexp = $languages['regexp'];
        $this->replacements = $languages['replacements'];
    }

    public function fix(string $fieldName, string $subject): string
    {
        $subject = parent::fix($fieldName, $subject);

        $subject = Regexp::split('#[\n,;&]|[, ]and #', $subject);
        $subject = array_filter(array_map('trim', $subject));
        $subject = array_map(function (string $language): string {
            $language = Regexp::replaceAll($this->replacements, $language);

            Regexp::match($this->regexp, $language, $matches);

            $language = $matches['language'];
            $suffix = $matches['prefix'] || ($matches['suffix'] ?? '') ? ' (limited)' : '';

            $language = StrUtils::ucfirst($language);

            return $language.$suffix;
        }, $subject);

        sort($subject);

        return implode("\n", $subject);
    }
}
