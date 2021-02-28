<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Artisan\Fields;
use App\Utils\Regexp\Replacements;

class UrlFixer extends StringFixer
{
    private Replacements $replacements;

    public function __construct(array $urls, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = new Replacements($urls['replacements'], 'i', $urls['commonRegexPrefix'], $urls['commonRegexSuffix']);
    }

    public function fix(string $fieldName, string $subject): string
    {
        $result = parent::fix($fieldName, $subject);

        if (Fields::URL_OTHER !== $fieldName) {
            $result = $this->replacements->do($result);
        }

        return $result;
    }
}
