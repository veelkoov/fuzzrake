<?php

declare(strict_types=1);

namespace App\Data\Species;

class SpeciesBuilder
{
    private const FLAG_HIDDEN_FLAG = 'i'; // Marks species considered valid, but which won't e.g. be available for filtering

    private readonly MutableSpecies $created;

    /**
     * @param array<string, psSubspecies> $species
     */
    public function __construct(array $species)
    {
        $this->created = new MutableSpecies();

        $this->fillCompleteListAndTreeFrom($species);
    }

    public function get(): Species
    {
        $list = $this->created->list->toList();
        $tree = array_map(fn (MutableSpecie $specie) => $list->getByName($specie->name), $this->created->tree);

        return new Species(
            $list,
            $tree,
        );
    }

    /**
     * @param array<string, psSubspecies> $species
     */
    private function fillCompleteListAndTreeFrom(array $species): void
    {
        foreach ($species as $flagsAndName => $subspecies) {
            $this->created->tree[] = $this->getUpdatedCompleteSpecie($flagsAndName, null, $subspecies);
        }
    }

    /**
     * @param psSubspecies $subspecies
     */
    private function getUpdatedCompleteSpecie(string $flagsAndName, ?MutableSpecie $parent, ?array $subspecies): MutableSpecie
    {
        [$flags, $name] = $this->splitSpecieFlagsName($flagsAndName);
        $hidden = self::hasHiddenFlag($flags);

        $specie = $this->created->list->getByNameOrCreate($name, $hidden);

        if ($hidden) {
            $specie->setHidden(true);
        }

        if (null !== $parent) {
            $specie->addParent($parent);
        }

        if (null !== $subspecies) {
            foreach ($subspecies as $childFlagsAndName => $childSubspecies) {
                $child = $this->getUpdatedCompleteSpecie($childFlagsAndName, $specie, $this->subspecies($childSubspecies));

                $specie->addChild($child);
            }
        }

        return $specie;
    }

    /**
     * Workaround for lack of recursion in type definition.
     *
     * @param psNextLevelSubspecies $subspecies
     *
     * @return psSubspecies
     */
    private function subspecies(?array $subspecies): ?array
    {
        return $subspecies; // @phpstan-ignore-line
    }

    /**
     * @return array{string, string}
     */
    private function splitSpecieFlagsName(string $specie): array
    {
        $parts = explode('_', $specie, 2);

        return 1 === count($parts) ? ['', $parts[0]] : [$parts[0], $parts[1]];
    }

    private static function hasHiddenFlag(string $flags): bool
    {
        return self::flagged($flags, self::FLAG_HIDDEN_FLAG);
    }

    private static function flagged(string $flags, string $flag): bool
    {
        return str_contains($flags, $flag);
    }
}
