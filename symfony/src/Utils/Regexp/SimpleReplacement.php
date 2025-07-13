<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use Composer\Pcre\Preg;
use Override;

class SimpleReplacement implements ReplacementInterface
{
    private readonly string $pattern;

    public function __construct(
        string $pattern,
        string $flags,
        private readonly string $replacement,
    ) {
        $pattern = str_replace('~', '\~', $pattern);
        $this->pattern = '~'.$pattern.'~'.$flags;
    }

    #[Override]
    public function do(string $input): string
    {
        return Preg::replace($this->pattern, $this->replacement, $input);
    }
}
