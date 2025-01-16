<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

use App\Filtering\FiltersData\Builder\Data\MutableSpecialItemList;

readonly class MutableFilterData
{
    public MutableSet $items;
    public MutableSpecialItemList $specialItems;

    public function __construct(
        MutableSpecialItem ...$specialItems,
    ) {
        $this->items = new MutableSet();
        $this->specialItems = new MutableSpecialItemList($specialItems);
    }
}
