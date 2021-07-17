<?php

declare(strict_types=1);

namespace App\Utils\Filters;

class FilterData
{
    private int $unknownCount = 0;
    private int $otherCount = 0;
    private Set $items;

    public function __construct(
        private bool $hasOther,
        private bool $hasUnknown = true,
    ) {
        $this->items = new Set();
    }

    public function incUnknownCount(int $number = 1): void
    {
        $this->unknownCount += $number;
    }

    public function incOtherCount(): void
    {
        ++$this->otherCount;
    }

    public function getUnknownCount(): int
    {
        return $this->unknownCount;
    }

    public function getOtherCount(): int
    {
        return $this->otherCount;
    }

    public function isHasOther(): bool
    {
        return $this->hasOther;
    }

    public function isHasUnknown(): bool
    {
        return $this->hasUnknown;
    }

    public function getItems(): Set
    {
        return $this->items;
    }
}
