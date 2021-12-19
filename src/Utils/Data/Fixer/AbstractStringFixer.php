<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;

class AbstractStringFixer implements FixerInterface
{
    private readonly Replacements $replacements;

    public function __construct(array $regexes)
    {
        $this->replacements = new Replacements($regexes['replacements'], 'i', $regexes['commonRegexPrefix'], $regexes['commonRegexSuffix']);
    }

    public function fix(string $fieldName, string $subject): string
    {
        return $this->replacements->do(trim($subject));
    }
}
