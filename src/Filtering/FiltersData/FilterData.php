<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableFilterData;

readonly class FilterData
{
    /**
     * @param list<Item>        $items
     * @param list<SpecialItem> $specialItems
     */
    public function __construct(
        public array $items,
        public array $specialItems,
    ) {
    }

    public static function from(MutableFilterData $source): self
    {
        return new FilterData(
            $source->items->getReadonlyList(),
            array_map(fn ($item) => SpecialItem::from($item), $source->specialItems),
        );
    }
}
