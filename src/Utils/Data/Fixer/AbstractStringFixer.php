<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;

class AbstractStringFixer implements FixerInterface
{
    private readonly Replacements $replacements;

    /**
     * @param psFixerConfig $regexes
     */
    public function __construct(array $regexes)
    {
        $this->replacements = new Replacements($regexes['replacements'], 'i', $regexes['regex_prefix'], $regexes['regex_suffix']);
    }

    public function fix(string $subject): string
    {
        return $this->replacements->do(trim($subject));
    }
}
