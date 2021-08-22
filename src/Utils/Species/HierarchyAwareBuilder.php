<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

class HierarchyAwareBuilder
{
    private const FLAG_PREFIX_REGEXP = '^(?<flags>[a-z]{1,2})_(?<specie>.+)$';
    private const FLAG_IGNORE_THIS_FLAG = 'i'; // Marks species considered valid, but which won't e.g. be available for filtering

    /**
     * @var Specie[] Associative: key = name, value = Specie object. Species fit for filtering
     */
    private array $flat;

    /**
     * @var Specie[] Species fit for filtering
     */
    private array $tree;

    /**
     * @var string[] Names of species considered valid by the validator (list of all, not only fit for filtering)
     */
    private array $validNames;

    public function __construct(array $species)
    {
        $this->flat = [];
        $this->tree = $this->getTreeFor($species);

        $this->validNames = $this->gatherValidChoices($species);
    }

    public function getFlat(): array
    {
        return $this->flat;
    }

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
     * @param array[]|string[] $species
     *
     * @return string[]
     */
    private function gatherValidChoices(array $species): array
    {
        $result = [];

        foreach ($species as $specie => $subspecies) {
            [, $specie] = $this->splitSpecieFlagsName($specie);

            $result[] = $specie;

            if (is_array($subspecies)) {
                $result = array_merge($result, $this->gatherValidChoices($subspecies));
            }
        }

        return $result;
    }

    private function getTreeFor(array $species, Specie $parent = null): array
    {
        $result = [];

        foreach ($species as $specieName => $subspecies) {
            [$flags, $specieName] = $this->splitSpecieFlagsName($specieName);

            $this->validNames[] = $specieName;

            if ($this->flagged($flags, self::FLAG_IGNORE_THIS_FLAG)) {
                continue;
            }

            $specie = $this->getUpdatedSpecie($specieName, $parent, $subspecies);

            $result[$specieName] = $specie;
        }

        return $result;
    }

    private function getUpdatedSpecie(string $specieName, ?Specie $parent, ?array $subspecies): Specie
    {
        $specie = $this->flat[$specieName] ??= new Specie($specieName);

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

    private function splitSpecieFlagsName(string $specie): array
    {
        try {
            return pattern(self::FLAG_PREFIX_REGEXP)->match($specie)
                ->findFirst(fn (Detail $match): array => [
                    $match->group('flags')->text(),
                    $match->group('specie')->text(),
                ])->orReturn(['', $specie]);
        } catch (NonexistentGroupException $exception) {
            throw new UnbelievableRuntimeException($exception);
        }
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function flagged(string $flags, string $flag): bool
    {
        return str_contains($flags, $flag);
    }
}
