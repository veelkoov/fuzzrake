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
    private const FLAG_IGNORE_THIS_FLAG = 'i'; // Marks species considered valid, but which won't e.g. be available for filtering

    /**
     * @var array<string, Specie> Associative: key = name, value = Specie object. Species fit for filtering
     */
    private array $flat;

    /**
     * @var Specie[] Species fit for filtering
     */
    private readonly array $tree;

    /**
     * @var string[] Names of species considered valid by the validator (list of all, not only fit for filtering)
     */
    private array $validNames;

    private readonly Pattern $flagPattern;

    /**
     * @param array<string, psSpecie> $species
     */
    public function __construct(array $species)
    {
        $this->flagPattern = pattern(self::FLAG_PREFIX_REGEXP);

        $this->flat = [];
        $this->tree = $this->getTreeFor($species);

        $this->validNames = [];
        $this->addValidNamesFrom($species);
    }

    /**
     * @return array<string, Specie>
     */
    public function getFlat(): array
    {
        return $this->flat;
    }

    /**
     * @return Specie[]
     */
    public function getTree(): array
    {
        return $this->tree;
    }

    /**
     * @return string[]
     */
    public function getValidNames(): array
    {
        return $this->validNames;
    }

    /**
     * @param array<string, psSpecie> $species
     */
    private function addValidNamesFrom(array $species): void
    {
        foreach ($species as $specie => $subspecies) {
            [, $specie] = $this->splitSpecieFlagsName($specie);

            if (!in_array($specie, $this->validNames)) {
                $this->validNames[] = $specie;
            }

            if (is_array($subspecies)) {
                $this->addValidNamesFrom($this->subspecies($subspecies));
            }
        }
    }

    /**
     * @param array<string, psSpecie> $species
     *
     * @return array<string, Specie>
     */
    private function getTreeFor(array $species, Specie $parent = null): array
    {
        $result = [];

        foreach ($species as $specieName => $subspecies) {
            [$flags, $specieName] = $this->splitSpecieFlagsName($specieName);

            if (null !== $subspecies) {
                $subspecies = $this->subspecies($subspecies);
            }

            $ignored = self::hasIgnoreFlag($flags);

            $result[$specieName] = $this->getUpdatedSpecie($specieName, $ignored, $parent, $subspecies);
            $this->validNames[] = $specieName;
        }

        return $result;
    }

    /**
     * @param array<string, psSubspecie> $subspecies
     *
     * @return array<string, psSpecie>
     */
    private function subspecies(array $subspecies): array
    {
        return $subspecies; // @phpstan-ignore-line - No recursion allowed
    }

    /**
     * @param array<string, psSpecie>|null $subspecies
     */
    private function getUpdatedSpecie(string $specieName, bool $ignored, ?Specie $parent, ?array $subspecies): Specie
    {
        if (null !== $parent && $parent->isIgnored()) {
            $ignored = true;
        }

        $specie = $this->flat[$specieName] ??= new Specie($specieName, $ignored);

        if (null !== $parent) {
            $specie->addParent($parent);
        }

        if (!empty($subspecies)) {
            foreach ($this->getTreeFor($subspecies, $specie) as $subspecie) {
                $specie->addChild($subspecie);
            }
        }

        return $specie;
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

    private static function hasIgnoreFlag(string $flags): bool
    {
        return self::flagged($flags, self::FLAG_IGNORE_THIS_FLAG);
    }

    /** @noinspection PhpSameParameterValueInspection */
    private static function flagged(string $flags, string $flag): bool
    {
        return str_contains($flags, $flag);
    }
}
