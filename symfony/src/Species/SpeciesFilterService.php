<?php

declare(strict_types=1);

namespace App\Species;

use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Data\SpecialItemList;
use App\Filtering\FiltersData\FilterData;
use App\Filtering\FiltersData\Item;
use App\Filtering\FiltersData\SpecialItem;
use App\Repository\CreatorRepository;
use App\Repository\CreatorSpecieRepository;
use App\Species\Hierarchy\Specie;
use App\Species\Hierarchy\Species;
use App\Species\Hierarchy\SpecieSet;
use Veelkoov\Debris\StringIntMap;

final class SpeciesFilterService
{
    private readonly Species $species;

    public function __construct(
        SpeciesService $speciesService,
        private readonly CreatorSpecieRepository $repository,
        private readonly CreatorRepository $creatorRepository,
    ) {
        $this->species = $speciesService->species;
    }

    public function getFilterData(): FilterData
    {
        $stats = $this->repository->getActiveCreatorsSpecieNamesToCount();
        $items = $this->getSpeciesList($this->species->getAsTree(), $stats);

        $allCount = $this->creatorRepository->countActive();
        $knownCount = $this->repository->countActiveCreatorsHavingSpeciesDefined();
        $unknown = SpecialItem::from(SpecialItems::newUnknown($allCount - $knownCount));

        return new FilterData($items, SpecialItemList::of($unknown));
    }

    private function getSpeciesList(SpecieSet $species, StringIntMap $stats): ItemList
    {
        return ItemList::mapFrom(
            $species->filter(static fn (Specie $specie) => !$specie->getHidden()),
            fn (Specie $specie) => $this->specieToStandardItem($specie, $stats),
        );
    }

    private function specieToStandardItem(Specie $specie, StringIntMap $stats): Item
    {
        return new Item(
            $specie->getName(),
            $specie->getName(),
            $stats->getOrDefault($specie->getName(), static fn () => 0),
            $this->getSpeciesList($specie->getChildren(), $stats),
        );
    }
}
