<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;

class UrlFixer extends StringFixer
{
    private readonly Replacements $replacements;

    public function __construct(array $urls, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = new Replacements($urls['replacements'], 'i', $urls['commonRegexPrefix'], $urls['commonRegexSuffix']);
    }

    public function fix(string $fieldName, string $subject): string
    {
        $result = parent::fix($fieldName, $subject);

        return $this->replacements->do($result);
    }
}
