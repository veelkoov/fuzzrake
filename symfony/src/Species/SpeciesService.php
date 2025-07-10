<?php

declare(strict_types=1);

namespace App\Species;

use App\Species\Hierarchy\MutableSpecie;
use App\Species\Hierarchy\MutableSpecies;
use App\Species\Hierarchy\Species;
use App\Utils\Regexp\Replacements;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\StringSet;

/**
 * @phpstan-type TSpecies             array<string, TSubspecies>
 * @phpstan-type TSubspecies          null|array<string, TNextLevelSubspecies>
 * @phpstan-type TNextLevelSubspecies null|array<string, mixed>
 */
final class SpeciesService
{
    public readonly Species $species;
    private readonly Replacements $fixerReplacements;

    /**
     * @param array{replacements: array<string, string>, regex_prefix: string, regex_suffix: string, leave_unchanged: string[], valid_choices: TSpecies} $speciesDefinitions
     */
    public function __construct(
        #[Autowire(param: 'species_definitions')]
        array $speciesDefinitions,
    ) {
        $species = new MutableSpecies();
        $this->species = $species;

        foreach ($speciesDefinitions['valid_choices'] as $nameOptFlag => $subspeciesData) {
            $species->addRootSpecie($this->createSpecie($species, $nameOptFlag, $subspeciesData));
        }

        $this->fixerReplacements = new Replacements($speciesDefinitions['replacements'], 'i', $speciesDefinitions['regex_prefix'], $speciesDefinitions['regex_suffix']);
    }

    public function getValidNames(): StringSet
    {
        return $this->species->getNames();
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
        $name = $hidden ? substr($nameOptFlag, 2) : $nameOptFlag;

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
}
