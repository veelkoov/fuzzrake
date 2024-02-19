<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use TRegx\CleanRegex\Pattern;

class SinceFixer implements FixerInterface
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
