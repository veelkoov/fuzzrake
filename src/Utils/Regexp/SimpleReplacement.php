<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use TRegx\CleanRegex\PatternInterface;

class SimpleReplacement implements ReplacementInterface
{
    private PatternInterface $pattern;

    public function __construct(
        string $pattern,
        string $flags,
        private string $replacement,
    ) {
        $this->pattern = pattern($pattern, $flags);
    }

    public function do(string $input): string
    {
        return $this->pattern->replace($input)->all()->withReferences($this->replacement);
    }
}
