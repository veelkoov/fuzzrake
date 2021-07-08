<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use TRegx\CleanRegex\PatternInterface;

class Matcher
{
    /**
     * @param PatternInterface[] $patterns KEY => pattern
     */
    public function __construct(
        private array $patterns,
    ) {
    }

    /**
     * @throws TrackerException
     */
    public function getKeyOfPatternMatching(string $match): string
    {
        foreach ($this->patterns as $key => $pattern) {
            if ($pattern->test($match)) {
                return $key;
            }
        }

        throw new TrackerException("Failed to find pattern, which matched '$match'");
    }
}
