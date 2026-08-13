<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Data;

use App\Filtering\FiltersData\Builder\MutableSpecialItem;
use App\Filtering\FiltersData\SpecialItem;
use Veelkoov\Debris\Vecs\Base\DVec;

/**
 * @extends DVec<SpecialItem>
 */
final class SpecialItemList extends DVec
{
    /**
     * @param iterable<MutableSpecialItem> $source
     */
    public static function from(iterable $source, int $totalForPopularity): SpecialItemList
    {
        return SpecialItemList::mapFrom($source, static fn (MutableSpecialItem $item) => SpecialItem::from($item, $totalForPopularity));
    }
}
