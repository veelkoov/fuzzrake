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
     * @param array{replacements: array<string, string>, regex_prefix: string, regex_suffix: string, leave_unchanged: string[], valid_choices: array<string, psSubspecies>} $speciesDefinitions
     */
    public function __construct(
        private readonly array $speciesDefinitions,
        private readonly ArtisanRepository $artisanRepository,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getValidNames(): array
    {
        return $this->getBuilder()->getValidNames();
    }

    /**
     * @return array<string, Specie>
     */
    public function getVisibleList(): array
    {
        return $this->getBuilder()->getVisibleList();
    }

    /**
     * @return list<Specie>
     */
    public function getVisibleTree(): array
    {
        return $this->getBuilder()->getVisibleTree();
    }

    public function getListFixerReplacements(): Replacements
    {
        return new Replacements($this->speciesDefinitions['replacements'], 'i',
            $this->speciesDefinitions['regex_prefix'], $this->speciesDefinitions['regex_suffix']);
    }

    /**
     * @return SpecieStats[]
     */
    public function getStats(): array
    {
        return (new StatsCalculator(Artisan::wrapAll($this->artisanRepository->getActive()), $this->getVisibleList()))->get();
    }

    private function getBuilder(): HierarchyAwareBuilder
    {
        return $this->builder ??= new HierarchyAwareBuilder($this->speciesDefinitions['valid_choices']);
    }
}
