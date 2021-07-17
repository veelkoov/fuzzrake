<?php

declare(strict_types=1);

namespace App\Utils\Filters;

use ArrayAccess;
use ArrayIterator;
use Iterator;
use IteratorAggregate;

class Set implements IteratorAggregate, ArrayAccess
{
    /**
     * @var Item[]
     */
    private array $items = [];

    public function addOrIncItem(string $key): void
    {
        if (!array_key_exists($key, $this->items)) {
            $this->items[$key] = new Item($key);
        }

        $this->items[$key]->incCount();
    }

    public function addComplexItem(string $key, string | Set $value, string $label, int $count): void
    {
        $this->items[$key] = new Item($value, $label, $count);
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

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function sort(): void
    {
        uasort($this->items, fn (Item $a, Item $b): int => strcmp($a->getLabel(), $b->getLabel()));
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset): Item
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
}
