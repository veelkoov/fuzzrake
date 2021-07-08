<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use TRegx\CleanRegex\PatternInterface;

class PatternsFactory
{
    /**
     * @var string[] PLACEHOLDER => Replacement
     */
    private array $regexPlaceholderReplacements;

    public function __construct(...$regexPlaceholderReplacements)
    {
        $this->regexPlaceholderReplacements = array_merge(...$regexPlaceholderReplacements);
    }

    /**
     * @param string[] $regexes
     *
     * @return PatternInterface[]
     */
    public function generateFrom(array $regexes): array
    {
        return array_map(fn ($regex) => pattern($this->resolvePlaceholders($regex), 's'), $regexes);
    }

    private function resolvePlaceholders(string $regex): string
    {
        do {
            $changed = false;

            foreach ($this->regexPlaceholderReplacements as $placeholder => $replacement) {
                $regex = str_replace($placeholder, $replacement, $regex, $count);

                $changed = $changed || $count > 0;
            }
        } while ($changed);

        return $regex;
    }
}
