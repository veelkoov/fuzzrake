<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Item;
use ArrayAccess;
use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Override;

/**
 * @implements ArrayAccess<string, MutableItem>
 * @implements IteratorAggregate<string, MutableItem>
 */
class MutableSet implements IteratorAggregate, ArrayAccess
{
    /**
     * @var array<string, MutableItem>
     */
    private array $items = [];

    public function addOrIncItem(string $valueAndLabel, int $count = 1): void
    {
        if (!array_key_exists($valueAndLabel, $this->items)) {
            $this->items[$valueAndLabel] = new MutableItem($valueAndLabel, $valueAndLabel);
        }

        $this->items[$valueAndLabel]->incCount($count);
    }

    public function addComplexItem(string $value, string $label, int $count, MutableSet $subitems = new MutableSet()): void
    {
        $this->items[$value] = new MutableItem($value, $label, $count, $subitems);
    }

    /**
     * @return array<string, MutableItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getReadonlyList(): ItemList
    {
        return ItemList::mapFrom($this->items, fn (MutableItem $item) => Item::from($item));
    }

    public function sort(): void
    {
        uasort($this->items, fn (MutableItem $a, MutableItem $b): int => strcmp($a->label, $b->label));
    }

    /**
     * @return Iterator<string, MutableItem>
     */
    #[Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->items);
    }

    #[Override]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    #[Override]
    public function offsetGet($offset): MutableItem
    {
        return $this->items[$offset];
    }

    #[Override]
    public function offsetSet($offset, $value): void
    {
        $this->items[$offset] = $value;
    }

    #[Override]
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
}
