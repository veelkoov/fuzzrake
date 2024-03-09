<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use App\Utils\Regexp\Replacements;

class UrlFixer extends StringFixer
{
    private readonly Replacements $replacements;

    /**
     * @param psFixerConfig $urls
     * @param psFixerConfig $strings
     */
    public function __construct(array $urls, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = new Replacements($urls['replacements'], 'i', $urls['regex_prefix'], $urls['regex_suffix']);
    }

    public function fix(string $subject): string
    {
        $result = parent::fix($subject);

        return $this->replacements->do($result);
    }
}
