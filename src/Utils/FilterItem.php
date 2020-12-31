<?php

declare(strict_types=1);

namespace App\Utils;

class FilterItem
{
    private string $label;
    private int $count;

    /**
     * @var int|string|FilterItems
     */
    private $value;

    /**
     * @param int|string|FilterItems $value
     */
    public function __construct($value, string $label = '', int $count = 0)
    {
        $this->value = $value;
        $this->label = $label ?: (string) $value;
        $this->count = $count;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return int|string|FilterItems
     */
    public function getValue()
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
