<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters\ValueChecker;

use function Psl\Iter\any;

class AnythingChecker implements ValueCheckerInterface
{
    /**
     * @param string[] $wantedItems
     */
    public function __construct(
        private readonly array $wantedItems
    ) {
    }

    public function matches(string $items, ?bool $matchedOther): bool
    {
        if (true === $matchedOther) {
            return true;
        }

        return [] !== $this->wantedItems && any($this->wantedItems, fn (string $item) => str_contains($items, $item));
    }
}
