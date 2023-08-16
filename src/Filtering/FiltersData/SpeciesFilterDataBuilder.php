<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Data\Species\Specie;
use App\Data\Species\Species;
use App\Data\Species\Stats\SpeciesStats;
use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\MutableSet;
use App\Filtering\FiltersData\Builder\SpecialItems;

final class SpeciesFilterDataBuilder
{
    private readonly FilterData $result;

    public static function for(Species $species, SpeciesStats $stats): FilterData
    {
        return (new self($species, $stats))->get();
    }

    private function __construct(
        private readonly Species $species,
        private readonly SpeciesStats $stats,
    ) {
        $result = new MutableFilterData(SpecialItems::newUnknown($this->stats->unknownCount));

        foreach ($this->getSpeciesFilterItemsFromArray($this->species->tree) as $item) {
            $result->items->addComplexItem($item->label, $item->value, $item->label, $item->getCount());
        }

        $this->result = new FilterData($result);
    }

    private function get(): FilterData
    {
        return $this->result;
    }

    /**
     * @param Specie[] $species
     *
     * @phpstan-param list<Specie> $species
     */
    private function getSpeciesFilterItemsFromArray(array $species): MutableSet
    {
        $result = new MutableSet();

        foreach ($species as $specie) {
            if (!$specie->hidden) {
                $result->addComplexItem($specie->name, $this->getSpeciesFilterItem($specie), $specie->name,
                    $this->stats->get($specie->name)?->realDoes ?? 0);
            }
        }

        return $result;
    }

    private function getSpeciesFilterItem(Specie $specie): MutableSet|string
    {
        if ($specie->isLeaf()) {
            return $specie->name;
        } else {
            return $this->getSpeciesFilterItemsFromArray($specie->getChildren());
        }
    }
}
