<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

class MutableSpecialItem
{
    private ?int $count; // TODO: #76 Species count, should not be nullable

    public function __construct(
        public readonly string $value,
        public readonly string $label,
        public readonly string $faIcon,
        ?int $count = 0,
    ) {
        $this->count = $count;
    }

    public function incCount(?int $number = 1): void
    {
        if (null !== $this->count && null !== $number) {
            $this->count += $number;
        }
    }

    public function getCount(): ?int
    {
        return $this->count;
    }
}
