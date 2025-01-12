<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableItem;
use App\Filtering\FiltersData\Data\ItemList;

readonly class Item
{
    public function __construct(
        public string $value,
        public string $label,
        public int $count,
        public ItemList $subitems = new ItemList(),
    ) {
    }

    public static function from(MutableItem $source): self
    {
        return new self(
            $source->value,
            $source->label,
            $source->getCount(),
            $source->subitems->getReadonlyList(),
        );
    }
}
