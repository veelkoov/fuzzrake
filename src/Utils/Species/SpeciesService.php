<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Utils\Regexp\Replacements;

class SpeciesService
{
    private ?HierarchyAwareBuilder $builder = null;

    /**
     * @param array{replacements: array<string, string>, regex_prefix: string, regex_suffix: string, leave_unchanged: string[], valid_choices: array<string, psSubspecies>} $speciesDefinitions
     */
    public function __construct(
        private readonly array $speciesDefinitions,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getValidNames(): array
    {
        return $this->getBuilder()->getCompleteList()->getNames();
    }

    public function getVisibleList(): SpeciesList
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

    public function getCompleteList(): SpeciesList
    {
        return $this->getBuilder()->getCompleteList();
    }

    public function getListFixerReplacements(): Replacements
    {
        return new Replacements($this->speciesDefinitions['replacements'], 'i',
            $this->speciesDefinitions['regex_prefix'], $this->speciesDefinitions['regex_suffix']);
    }

    private function getBuilder(): HierarchyAwareBuilder
    {
        return $this->builder ??= new HierarchyAwareBuilder($this->speciesDefinitions['valid_choices']);
    }
}
