<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Regexp\Replacements;

class SpeciesService
{
    private ?HierarchyAwareBuilder $builder = null;

    /**
     * @param array{replacements: string[], regex_prefix: string, regex_suffix: string, leave_unchanged: string[], valid_choices: string[]} $speciesDefinitions
     */
    public function __construct(
        private readonly array $speciesDefinitions,
        private readonly ArtisanRepository $artisanRepository,
    ) {
    }

    /**
     * @return string[] Names of species considered valid by the validator (list of all, not only fit for filtering)
     */
    public function getValidNames(): array
    {
        return $this->getBuilder()->getValidNames();
    }

    /**
     * @return Specie[] Associative: key = name, value = Specie object. Species fit for filtering
     */
    public function getFlat(): array
    {
        return $this->getBuilder()->getFlat();
    }

    /**
     * @return Specie[] Species fit for filtering
     */
    public function getTree(): array
    {
        return $this->getBuilder()->getTree();
    }

    public function getListFixerReplacements(): Replacements
    {
        return new Replacements($this->speciesDefinitions['replacements'], 'i',
            $this->speciesDefinitions['regex_prefix'], $this->speciesDefinitions['regex_suffix']);
    }

    /**
     * @return string[]
     */
    public function getListFixerUnsplittable(): array
    {
        return $this->speciesDefinitions['leave_unchanged'];
    }

    /**
     * @return SpecieStats[]
     */
    public function getStats(): array
    {
        return (new StatsCalculator(Artisan::wrapAll($this->artisanRepository->getActive()), $this->getFlat()))->get();
    }

    private function getBuilder(): HierarchyAwareBuilder
    {
        return $this->builder ??= new HierarchyAwareBuilder($this->speciesDefinitions['valid_choices']);
    }
}
