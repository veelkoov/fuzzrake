<?php

declare(strict_types=1);

namespace App\Twig\Utils;

use TRegx\CleanRegex\Pattern;
use TRegx\CleanRegex\PatternList;

use function Psl\Vec\map;

class HumanReadableRegexes
{
    private const PATTERNS = [
        '\(\?<!.+?\)',
        '\(\?!.+?\)',
        '\([^a-z]+?\)',
        '[()?]',
        '\[.+?\]',
    ];

    private PatternList $patterns;

    public function __construct()
    {
        $this->patterns = Pattern::list(map(self::PATTERNS, fn ($item) => pattern($item, 'i')));
    }

    public function makeReadable(string $input): string
    {
        return strtoupper($this->patterns->prune($input));
    }
}
