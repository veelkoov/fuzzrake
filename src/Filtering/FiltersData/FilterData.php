<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Data\SpecialItemList;

readonly class FilterData
{
    public function __construct(
        public ItemList $items,
        public SpecialItemList $specialItems,
    ) {
    }

    public static function from(MutableFilterData $source, int $totalForPopularity): self
    {
        return new FilterData(
            ItemList::from($source->items, $totalForPopularity),
            SpecialItemList::from($source->specialItems, $totalForPopularity),
        );
    }
}
