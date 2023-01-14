<?php

declare(strict_types=1);

namespace App\Utils\Species;

class HierarchyAwareBuilder
{
    private const FLAG_HIDDEN_FLAG = 'i'; // Marks species considered valid, but which won't e.g. be available for filtering

    private readonly SpeciesList $completeList;
    private readonly SpeciesList $visibleList;

    /**
     * @var list<Specie>
     */
    private array $completeTree = [];

    /**
     * @var list<Specie>
     */
    private array $visibleTree = [];

    /**
     * @param array<string, psSubspecies> $species
     */
    public function __construct(array $species)
    {
        $this->completeList = new SpeciesList();
        $this->visibleList = new SpeciesList();

        $this->fillCompleteListAndTreeFrom($species);
        $this->createVisibleListAndTree();
    }

    /**
     * @param array<string, psSubspecies> $species
     */
    private function fillCompleteListAndTreeFrom(array $species): void
    {
        foreach ($species as $flagsAndName => $subspecies) {
            $this->completeTree[] = $this->getUpdatedCompleteSpecie($flagsAndName, null, $subspecies);
        }
    }

    /**
     * @param psSubspecies $subspecies
     */
    private function getUpdatedCompleteSpecie(string $flagsAndName, ?Specie $parent, ?array $subspecies): Specie
    {
        [$flags, $name] = $this->splitSpecieFlagsName($flagsAndName);
        $hidden = self::hasHiddenFlag($flags);

        $specie = $this->completeList->getByNameOrCreate($name, $hidden);

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

    private function createVisibleListAndTree(): void
    {
        foreach ($this->completeTree as $completeSpecie) {
            $visibleSpecie = $this->getUpdatedVisibleSpecie($completeSpecie);

            if (null !== $visibleSpecie) {
                $this->visibleTree[] = $visibleSpecie;
            }
        }
    }

    private function getUpdatedVisibleSpecie(Specie $completeSpecie): ?Specie
    {
        if ($completeSpecie->isHidden()) {
            $visibleSpecie = null;
        } else {
            $name = $completeSpecie->getName();
            $visibleSpecie = $this->visibleList->getByNameOrCreate($name, false);
        }

        foreach ($completeSpecie->getChildren() as $completeChild) {
            $visibleChild = $this->getUpdatedVisibleSpecie($completeChild);

            if (null !== $visibleSpecie && null !== $visibleChild) {
                $visibleChild->addParentTwoWay($visibleSpecie);
            }
        }

        return $visibleSpecie;
    }

    /**
     * @return list<Specie>
     */
    public function getCompleteTree(): array
    {
        return $this->completeTree;
    }

    /**
     * @return list<Specie>
     */
    public function getVisibleTree(): array
    {
        return $this->visibleTree;
    }

    public function getCompleteList(): SpeciesList
    {
        return $this->completeList;
    }

    public function getVisibleList(): SpeciesList
    {
        return $this->visibleList;
    }
}
