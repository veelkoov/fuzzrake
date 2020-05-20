<?php

declare(strict_types=1);

namespace App\Utils;

use ArrayAccess;

class FilterItems implements ArrayAccess
{
    private int $unknownCount = 0;
    private int $otherCount = 0;
    private bool $hasOther;

    /**
     * @var FilterItem[]
     */
    private array $items = [];

    public function __construct(bool $hasOther)
    {
        $this->hasOther = $hasOther;
    }

    public function addOrIncItem(string $key): void
    {
        if (!array_key_exists($key, $this->items)) {
            $this->items[$key] = new FilterItem($key);
        }

        $this->items[$key]->incCount();
    }

    /**
     * @param int|string|FilterItems $value
     */
    public function addComplexItem(string $key, $value, string $label, int $count): void
    {
        $this->items[$key] = new FilterItem($value, $label, $count);
    }

    public function hasComplexItem(): bool
    {
        foreach ($this->items as $item) {
            if ($item->isComplex()) {
                return true;
            }
        }

        return false;
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

    /**
     * @return FilterItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function sort(): void
    {
        uasort($this->items, function (FilterItem $a, FilterItem $b): int {
            return strcmp($a->getLabel(), $b->getLabel());
        });
    }

    public function __get(string $key): FilterItem
    {
        return $this->items[$key];
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}
