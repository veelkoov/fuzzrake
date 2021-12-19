<?php

declare(strict_types=1);

namespace App\Utils\Filters;

class FilterData
{
    private readonly Set $items;
    private readonly array $specialItems;

    public function __construct(
        SpecialItem ...$specialItems,
    ) {
        $this->items = new Set();
        $this->specialItems = $specialItems;
    }

    public function getItems(): Set
    {
        return $this->items;
    }

    /**
     * @return SpecialItem[]
     */
    public function getSpecialItems(): array
    {
        return $this->specialItems;
    }
}
