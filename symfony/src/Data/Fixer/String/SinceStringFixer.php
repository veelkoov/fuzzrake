<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use App\Data\Fixer\StringFixerInterface;
use TRegx\CleanRegex\Pattern;

final class SinceStringFixer implements StringFixerInterface
{
    private readonly Pattern $pattern;

    public function __construct()
    {
        $this->pattern = pattern('(\d{4})-(\d{2})(?:-\d{2})?');
    }

    public function fix(string $subject): string
    {
        return $this->pattern
            ->replace($subject)
            ->withReferences('$1-$2');
    }
}
