<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use App\Data\Fixer\StringFixerInterface;
use App\Utils\Regexp\Replacements;

class ConfigurableStringFixer implements StringFixerInterface
{
    private readonly Replacements $replacements;

    /**
     * @param psFixerConfig $regexes
     */
    public function __construct(array $regexes, string $flags = 'i')
    {
        $this->replacements = new Replacements($regexes['replacements'], $flags, $regexes['regex_prefix'], $regexes['regex_suffix']);
    }

    public function fix(string $subject): string
    {
        return $this->replacements->do(trim($subject));
    }
}
