<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

class MutableItem
{
    public function __construct(
        public readonly string $value,
        public readonly string $label,
        private int $count = 0,
        public readonly MutableSet $subitems = new MutableSet(),
    ) {
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function incCount(int $number = 1): void
    {
        $this->count += $number;
    }
}
