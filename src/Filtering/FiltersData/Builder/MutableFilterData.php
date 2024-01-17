<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

class MutableFilterData
{
    public readonly MutableSet $items;

    /**
     * @var MutableSpecialItem[]
     */
    public array $specialItems;

    public function __construct(
        MutableSpecialItem ...$specialItems,
    ) {
        $this->items = new MutableSet();
        $this->specialItems = $specialItems;
    }
}
