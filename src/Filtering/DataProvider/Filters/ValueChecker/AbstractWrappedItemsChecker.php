<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters\ValueChecker;

use Psl\Vec;

abstract class AbstractWrappedItemsChecker implements ValueCheckerInterface
{
    /**
     * @var list<string>
     */
    protected readonly array $wantedItems;

    /**
     * @param list<string> $wantedItems
     */
    public function __construct(
        array $wantedItems
    ) {
        $this->wantedItems = Vec\map($wantedItems, fn (string $item) => "\n$item\n");
    }
}
