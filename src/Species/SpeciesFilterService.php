<?php

declare(strict_types=1);

namespace App\Species;

use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Data\SpecialItemList;
use App\Filtering\FiltersData\FilterData;
use App\Filtering\FiltersData\Item;
use App\Filtering\FiltersData\SpecialItem;
use App\Repository\CreatorSpecieRepository;
use App\Service\DataService;
use App\Species\Hierarchy\Specie;
use App\Species\Hierarchy\Species;
use App\Species\Hierarchy\SpecieSet;
use Veelkoov\Debris\Maps\StringToInt;

final class SpeciesFilterService
{
    private readonly Species $species;

    public function __construct(
        SpeciesService $speciesService,
        private readonly CreatorSpecieRepository $repository,
        private readonly DataService $dataService,
    ) {
        $this->species = $speciesService->species;
    }

    public function getFilterData(): FilterData
    {
        $allCount = $this->dataService->countActiveCreators();
        $stats = $this->repository->getActiveCreatorsSpecieNamesToCount();
        $items = $this->getSpeciesList($this->species->getAsTree(), $stats, $allCount);

        $knownCount = $this->repository->countActiveCreatorsHavingSpeciesDefined();
        $unknown = SpecialItem::from(SpecialItems::newUnknown($allCount - $knownCount), $allCount);

        return new FilterData($items, SpecialItemList::of($unknown));
    }

    private function getSpeciesList(SpecieSet $species, StringToInt $stats, int $totalForPopularity): ItemList
    {
        return ItemList::mapFrom(
            $species->filter(static fn (Specie $specie) => !$specie->getHidden()),
            fn (Specie $specie) => $this->specieToStandardItem($specie, $stats, $totalForPopularity),
        );
    }

    private function specieToStandardItem(Specie $specie, StringToInt $stats, int $totalForPopularity): Item
    {
        $count = $stats->getOrDefaultOf($specie->getName(), 0);

        return new Item(
            $specie->getName(),
            $specie->getName(),
            $count,
            Item::calcPopular($count, $totalForPopularity),
            $this->getSpeciesList($specie->getChildren(), $stats, $totalForPopularity),
        );
    }
}
