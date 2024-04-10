<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters\ValueChecker;

use function Psl\Iter\all;
use function Psl\Iter\contains;

class EverythingChecker extends AbstractWrappedItemsChecker
{
    /**
     * @param list<string> $items
     */
    public function matches(array $items, ?bool $matchedOther): bool
    {
        if ([] === $this->wantedItems) {
            return true === $matchedOther;
        }

        return false !== $matchedOther && all($this->wantedItems, fn (string $item) => contains($items, $item));
    }
}
