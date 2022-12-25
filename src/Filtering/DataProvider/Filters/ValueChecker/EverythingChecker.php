<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters\ValueChecker;

use function Psl\Iter\all;

class EverythingChecker implements ValueCheckerInterface
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
        if ([] === $this->wantedItems) {
            return true === $matchedOther;
        }

        return false !== $matchedOther && all($this->wantedItems, fn (string $item) => str_contains($items, $item));
    }
}
