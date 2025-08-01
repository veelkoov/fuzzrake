<?php

declare(strict_types=1);

namespace App\Tracking\Patterns;

use App\Utils\Collections\StringList;
use App\Utils\Regexp\Replacements;
use Veelkoov\Debris\Maps\StringToString;

class Patterns
{
    public readonly Replacements $cleaners;
    public readonly Replacements $falsePositives;
    public readonly StringList $offersStatuses;

    public function __construct(RegexesLoader $regexesLoader)
    {
        $this->cleaners = new Replacements($regexesLoader->cleaners, 's');
        $this->falsePositives = new Replacements(StringToString::fromKeys($regexesLoader->falsePositives,
            static fn () => 'FALSE_POSITIVE')->toArray(), 'sxJ');

        $this->offersStatuses = $regexesLoader->offersStatuses->mapInto(self::regexToPattern(...), new StringList());
    }

    public static function regexToPattern(string $regex): string
    {
        return '~'.str_replace('~', '\~', $regex).'~sxnJ';
    }
}
