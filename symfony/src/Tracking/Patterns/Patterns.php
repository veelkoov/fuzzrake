<?php

declare(strict_types=1);

namespace App\Tracking\Patterns;

use App\Utils\Regexp\Replacements;
use Veelkoov\Debris\Maps\StringToString;

class Patterns
{
    public readonly Replacements $cleaners;
    public readonly Replacements $falsePositives;

    public function __construct(RegexesLoader $regexesLoader)
    {
        $this->cleaners = new Replacements($regexesLoader->cleaners, 's');
        $this->falsePositives = new Replacements(StringToString::fromKeys($regexesLoader->falsePositives,
            static fn () => 'FALSE_POSITIVE')->toArray(), 'sxJ');
    }
}
