<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

class Replacements
{
    /**
     * @var ReplacementInterface[]
     */
    private array $replacements = [];

    /**
     * @param iterable<string, string> $replacements
     */
    public function __construct(iterable $replacements, string $flags, string $prefix = '', string $suffix = '')
    {
        foreach ($replacements as $pattern => $replacement) {
            $this->replacements[] = new SimpleReplacement("$prefix$pattern$suffix", $flags, $replacement);
        }
    }

    public function do(string $input): string
    {
        $result = $input;

        foreach ($this->replacements as $replacement) {
            $result = $replacement->do($result);
        }

        return $result;
    }
}
