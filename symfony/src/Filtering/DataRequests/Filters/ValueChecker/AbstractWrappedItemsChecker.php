<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters\ValueChecker;

abstract class AbstractWrappedItemsChecker implements ValueCheckerInterface
{
    /**
     * @param list<string> $wantedItems
     */
    public function __construct(
        protected readonly array $wantedItems
    ) {
    }
}
