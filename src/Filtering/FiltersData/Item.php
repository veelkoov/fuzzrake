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
        public float $popularity,
        public ItemList $subitems = new ItemList(),
    ) {
    }

    public static function from(MutableItem $source, int $totalForPopularity): self
    {
        return new self(
            $source->value,
            $source->label,
            $source->getCount(),
            self::calcPopular($source->getCount(), $totalForPopularity),
            ItemList::from($source->subitems, $totalForPopularity),
        );
    }

    public static function calcPopular(int $count, int $total): float
    {
        if ($total <= 0.0) {
            return 0.0; // Better have falsified stats than failure due to bug in a less significant feature
        }

        return $count / $total;
    }
}
