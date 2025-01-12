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

    public static function from(MutableFilterData $source): self
    {
        return new FilterData(
            $source->items->getReadonlyList(),
            SpecialItemList::map($source->specialItems, fn ($item) => SpecialItem::from($item)),
        );
    }
}
