<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Data;

use App\Filtering\FiltersData\Builder\MutableItem;
use App\Filtering\FiltersData\Item;
use Veelkoov\Debris\Vecs\Base\DVec;

/**
 * @extends DVec<Item>
 */
final class ItemList extends DVec
{
    /**
     * @param iterable<MutableItem> $source
     */
    public static function from(iterable $source, int $totalForPopularity): ItemList
    {
        return ItemList::mapFrom($source, static fn (MutableItem $item) => Item::from($item, $totalForPopularity));
    }
}
