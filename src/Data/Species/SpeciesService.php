<?php

declare(strict_types=1);

namespace App\Data\Species;

use App\Utils\Regexp\Replacements;
use RuntimeException;

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

    /**
     * @return list<Specie>
     */
    public function getCompleteTree(): array
    {
        return $this->getBuilder()->getCompleteTree();
    }

    public function getListFixerReplacements(): Replacements
    {
        return new Replacements($this->speciesDefinitions['replacements'], 'i',
            $this->speciesDefinitions['regex_prefix'], $this->speciesDefinitions['regex_suffix']);
    }

    private function getBuilder(): HierarchyAwareBuilder
    {
        if (null === $this->builder) {
            $this->builder = new HierarchyAwareBuilder($this->speciesDefinitions['valid_choices']);

            $this->validateTopSpeciesAreSeparateTrees();
        }

        return $this->builder; // @phpstan-ignore-line False-positive
    }

    private function validateTopSpeciesAreSeparateTrees(): void
    {
        $speciesTrees = array_map(
            fn (Specie $specie) => $specie->getSelfAndDescendants(),
            $this->getBuilder()->getCompleteTree(),
        );

        $specieNamesTrees = array_map(
            fn (array $species) => array_map(fn (Specie $specie) => $specie->getName(), $species),
            $speciesTrees,
        );

        $sharedSpecieNames = array_intersect(...$specieNamesTrees);

        if ([] !== $sharedSpecieNames) {
            throw new RuntimeException('Species configuration error: species shared between root species: '.implode(', ', $sharedSpecieNames));
        }
    }
}
