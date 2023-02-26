<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Data\Stats\SpeciesStats;
use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\MutableSet;
use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Utils\Species\Specie;

class SpeciesFilterDataCalculator
{
    private readonly FilterData $result;

    /**
     * @param list<Specie> $visibleTree
     */
    public function __construct(
        private readonly array $visibleTree,
        private readonly SpeciesStats $stats,
    ) {
        $result = new MutableFilterData(SpecialItems::newUnknown($this->stats->unknownCount));

        foreach ($this->getSpeciesFilterItemsFromArray($this->visibleTree) as $item) {
            $result->items->addComplexItem($item->label, $item->value, $item->label, $item->getCount());
        }

        $this->result = new FilterData($result);
    }

    /**
     * @param list<Specie> $visibleTree
     */
    public static function from(array $visibleTree, SpeciesStats $stats): self
    {
        return new self($visibleTree, $stats);
    }

    /**
     * @param list<Specie> $species
     */
    private function getSpeciesFilterItemsFromArray(array $species): MutableSet
    {
        $result = new MutableSet();

        foreach ($species as $specie) {
            if (!$specie->isHidden()) {
                $result->addComplexItem($specie->getName(), $this->getSpeciesFilterItem($specie), $specie->getName(),
                    $this->stats->get($specie->getName())?->realDoes ?? 0); // FIXME: "Other" don't work properly
            }
        }

        return $result;
    }

    private function getSpeciesFilterItem(Specie $specie): MutableSet|string
    {
        if ($specie->isLeaf()) {
            return $specie->getName();
        } else {
            return $this->getSpeciesFilterItemsFromArray($specie->getChildren());
        }
    }

    public function get(): FilterData
    {
        return $this->result;
    }
}
