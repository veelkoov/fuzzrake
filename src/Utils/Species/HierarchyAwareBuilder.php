<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

class HierarchyAwareBuilder
{
    private const FLAG_PREFIX_REGEXP = '^(?<flags>[a-z]{1,2})_(?<specie>.+)$';
    private const FLAG_HIDDEN_FLAG = 'i'; // Marks species considered valid, but which won't e.g. be available for filtering

    /**
     * @var array<string, Specie>
     */
    private array $completeList = [];

    /**
     * @var array<string, Specie>
     */
    private array $visibleList = [];

    /**
     * @var list<Specie>
     */
    private array $completeTree = [];

    /**
     * @var list<Specie>
     */
    private array $visibleTree = [];

    private readonly Pattern $flagPattern;

    /**
     * @param array<string, psSubspecies> $species
     */
    public function __construct(array $species)
    {
        $this->flagPattern = pattern(self::FLAG_PREFIX_REGEXP);

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

        $specie = $this->completeList[$name] ??= new Specie($name, $hidden);

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
     * @return array{0: string, 1: string}
     */
    private function splitSpecieFlagsName(string $specie): array
    {
        try {
            return $this->flagPattern->match($specie)
                ->findFirst()->map(fn (Detail $match): array => [
                    $match->group('flags')->text(),
                    $match->group('specie')->text(),
                ])->orReturn(['', $specie]);
        } catch (NonexistentGroupException $exception) { // @codeCoverageIgnoreStart
            throw new UnbelievableRuntimeException($exception);
        } // @codeCoverageIgnoreEnd
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
            $visibleSpecie = $this->visibleList[$name] ??= new Specie($name, false);
        }

        foreach ($completeSpecie->getChildren() as $completeChild) {
            $visibleChild = $this->getUpdatedVisibleSpecie($completeChild);

            if (null !== $visibleSpecie && null !== $visibleChild) {
                $visibleSpecie->addChild($visibleChild);
                $visibleChild->addParent($visibleSpecie);
            }
        }

        return $visibleSpecie;
    }

    /**
     * @return list<string>
     */
    public function getValidNames(): array
    {
        return array_keys($this->completeList);
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

    /**
     * @return array<string, Specie>
     */
    public function getCompleteList(): array
    {
        return $this->completeList;
    }

    /**
     * @return array<string, Specie>
     */
    public function getVisibleList(): array
    {
        return $this->visibleList;
    }
}
