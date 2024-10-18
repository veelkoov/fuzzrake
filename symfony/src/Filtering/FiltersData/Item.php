<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableItem;

readonly class Item
{
    /**
     * @param list<Item> $subitems
     */
    public function __construct(
        public string $value,
        public string $label,
        public int $count,
        public array $subitems = [],
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
