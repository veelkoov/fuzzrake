<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use Override;
use TRegx\CleanRegex\Pattern;

class SimpleReplacement implements ReplacementInterface
{
    private readonly Pattern $pattern;

    public function __construct(
        string $pattern,
        string $flags,
        private readonly string $replacement,
    ) {
        $this->pattern = pattern($pattern, $flags);
    }

    #[Override]
    public function do(string $input): string
    {
        return $this->pattern->replace($input)->withReferences($this->replacement);
    }
}
