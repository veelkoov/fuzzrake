<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters\ValueChecker;

use function Psl\Iter\any;
use function Psl\Iter\contains;

class AnythingChecker extends AbstractWrappedItemsChecker
{
    /**
     * @param list<string> $items
     */
    public function matches(array $items, ?bool $matchedOther): bool
    {
        if (true === $matchedOther) {
            return true;
        }

        return [] !== $this->wantedItems && any($this->wantedItems, fn (string $item) => contains($items, $item));
    }
}
