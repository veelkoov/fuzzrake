<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters\ValueChecker;

use function Psl\Iter\any;

class AnythingChecker extends AbstractWrappedItemsChecker
{
    public function matches(string $items, ?bool $matchedOther): bool
    {
        if (true === $matchedOther) {
            return true;
        }

        $items = "\n$items\n";

        return [] !== $this->wantedItems && any($this->wantedItems, fn (string $item) => str_contains($items, $item));
    }
}
