<?php

declare(strict_types=1);

namespace App\Utils;

class FilterItem
{
    public function __construct(
        private int | string | FilterItems $value,
        private string $label = '',
        private int $count = 0,
    ) {
        $this->label = $label ?: (string) $value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): int | string | FilterItems
    {
        return $this->value;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function incCount(int $number = 1): void
    {
        $this->count += $number;
    }

    public function isComplex(): bool
    {
        return $this->value instanceof FilterItems;
    }
}
