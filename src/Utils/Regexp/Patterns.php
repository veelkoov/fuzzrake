<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use Composer\Pcre\Preg;

final class Patterns
{
    /**
     * @var list<Pattern>
     */
    private readonly array $patterns;

    /**
     * @var list<string>
     */
    private readonly array $compiled;

    /**
     * @param iterable<string> $regexes
     */
    public function __construct(iterable $regexes, string $flags = '')
    {
        $this->patterns = iter_mapl($regexes, static fn ($regex) => new Pattern($regex, $flags));
        $this->compiled = array_map(static fn (Pattern $pattern) => $pattern->compiled, $this->patterns);
    }

    public function prune(string $input): string
    {
        return Preg::replace($this->compiled, '', $input);
    }
}
