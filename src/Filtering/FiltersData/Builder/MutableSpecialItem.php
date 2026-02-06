<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

class MutableSpecialItem
{
    public function __construct(
        public readonly string $value,
        public readonly string $label,
        public readonly string $faIcon,
        private int $count = 0,
    ) {
    }

    public function incCount(int $number = 1): void
    {
        $this->count += $number;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
