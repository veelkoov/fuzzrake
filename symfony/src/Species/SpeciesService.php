<?php

declare(strict_types=1);

namespace App\Species;

use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Data\SpecialItemList;
use App\Filtering\FiltersData\FilterData;
use App\Filtering\FiltersData\Item;
use App\Filtering\FiltersData\SpecialItem;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Repository\CreatorSpecieRepository;
use App\Utils\Regexp\Replacements;
use Psl\Str;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\StringIntMap;
use Veelkoov\Debris\StringList;

/**
 * @phpstan-type TSpecies             array<string, TSubspecies>
 * @phpstan-type TSubspecies          null|array<string, TNextLevelSubspecies>
 * @phpstan-type TNextLevelSubspecies null|array<string, mixed>
 */
class SpeciesService
{
    public readonly Species $species;
    private readonly Replacements $fixerReplacements;

    /**
     * @param array{replacements: array<string, string>, regex_prefix: string, regex_suffix: string, leave_unchanged: string[], valid_choices: TSpecies} $speciesDefinitions
     */
    public function __construct(
        #[Autowire(param: 'species_definitions')]
        array $speciesDefinitions,
        private readonly CreatorSpecieRepository $repository,
        private readonly CreatorRepository $creatorRepository,
    ) {
        $species = new MutableSpecies();
        $this->species = $species;

        foreach ($speciesDefinitions['valid_choices'] as $nameOptFlag => $subspeciesData) {
            $species->addRootSpecie($this->createSpecie($species, $nameOptFlag, $subspeciesData));
        }

        $this->fixerReplacements = new Replacements($speciesDefinitions['replacements'], 'i', $speciesDefinitions['regex_prefix'], $speciesDefinitions['regex_suffix']);
    }

    public function getValidNames(): StringList
    {
        return new StringList($this->species->getNames());
    }

    public function getListFixerReplacements(): Replacements
    {
        return $this->fixerReplacements;
    }

    /**
     * @param TSubspecies $subspeciesData
     */
    private function createSpecie(MutableSpecies $species, string $nameOptFlag, ?array $subspeciesData): MutableSpecie
    {
        $hidden = str_starts_with($nameOptFlag, 'i_');
        $name = Str\strip_prefix($nameOptFlag, 'i_');

        $specie = $species->getByNameCreatingMissing($name, $hidden);

        if ($specie->getHidden() !== $hidden) {
            throw new SpecieException("Repeated specie $name was declared hidden={$specie->getHidden()} and now is declared hidden=$hidden.");
        }

        if (null === $subspeciesData) {
            return $specie;
        }

        foreach ($subspeciesData as $subNameOptFlag => $subsubspeciesData) {
            $specie->addChild($this->createSpecie($species, $subNameOptFlag, $subsubspeciesData)); // @phpstan-ignore argument.type (Recurrence not possible to typehint)
        }

        return $specie;
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
            $stats->getOrDefault($specie->getName(), 0),
            $this->getSpeciesList($specie->getChildren(), $stats),
        );
    }
}
