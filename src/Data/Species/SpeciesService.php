<?php

declare(strict_types=1);

namespace App\Data\Species;

use App\Data\Species\Exceptions\SharedRootSpecieException;
use App\Data\Species\Stats\SpeciesStats;
use App\Data\Species\Stats\SpeciesStatsBuilder;
use App\Repository\ArtisanRepository;
use App\Service\Cache;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Regexp\Replacements;
use App\ValueObject\CacheTags;

class SpeciesService
{
    private ?Species $species = null;

    /**
     * @param array{replacements: array<string, string>, regex_prefix: string, regex_suffix: string, leave_unchanged: string[], valid_choices: array<string, psSubspecies>} $speciesDefinitions
     */
    public function __construct(
        private readonly array $speciesDefinitions,
        private readonly ArtisanRepository $artisanRepository,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getValidNames(): array
    {
        return $this->getSpecies()->list->getNames();
    }

    public function getListFixerReplacements(): Replacements
    {
        return new Replacements($this->speciesDefinitions['replacements'], 'i',
            $this->speciesDefinitions['regex_prefix'], $this->speciesDefinitions['regex_suffix']);
    }

    public function getSpecies(): Species
    {
        return $this->species ??= $this->createSpecies();
    }

    private function createSpecies(): Species
    {
        $result = (new SpeciesBuilder($this->speciesDefinitions['valid_choices']))->get();

        $speciesTrees = array_map(
            fn (Specie $specie) => $specie->getSelfAndDescendants(),
            $result->tree,
        );

        $specieNamesTrees = array_map(
            fn (array $species) => array_map(fn (Specie $specie) => $specie->name, $species),
            $speciesTrees,
        );

        $sharedSpecieNames = array_intersect(...$specieNamesTrees);

        if ([] !== $sharedSpecieNames) {
            throw new SharedRootSpecieException($sharedSpecieNames);
        }

        return $result;
    }

    public function getStats(): SpeciesStats
    {
        return $this->cache->getCached('SpeciesService.getStats', CacheTags::ARTISANS, function (): SpeciesStats {
            $artisans = Artisan::wrapAll($this->artisanRepository->getActive());

            return SpeciesStatsBuilder::for($this->getSpecies()->list)->add($artisans)->get();
        });
    }
}
