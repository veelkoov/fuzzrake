<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters\ValueChecker;

use function Psl\Iter\all;

class EverythingChecker extends AbstractWrappedItemsChecker
{
    public function matches(string $items, ?bool $matchedOther): bool
    {
        if ([] === $this->wantedItems) {
            return true === $matchedOther;
        }

        $items = "\n$items\n";

        return false !== $matchedOther && all($this->wantedItems, fn (string $item) => str_contains($items, $item));
    }
}
