<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\MutableSpecialItem;

readonly class FilterData
{
    /**
     * @var list<Item>
     */
    public array $items;

    /**
     * @var list<SpecialItem>
     */
    public array $specialItems;

    public function __construct(MutableFilterData $source)
    {
        $this->items = $source->items->getReadonlyList();
        $this->specialItems = array_map(fn (MutableSpecialItem $item) => new SpecialItem($item), $source->specialItems);
    }
}
