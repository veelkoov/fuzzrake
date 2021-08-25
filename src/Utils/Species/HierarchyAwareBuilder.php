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

        $this->validNames = [];
        $this->addValidNamesFrom($species);
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
     */
    private function addValidNamesFrom(array $species): void
    {
        foreach ($species as $specie => $subspecies) {
            [, $specie] = self::splitSpecieFlagsName($specie);

            if (!in_array($specie, $this->validNames)) {
                $this->validNames[] = $specie;
            }

            if (is_array($subspecies)) {
                $this->addValidNamesFrom($subspecies);
            }
        }
    }

    private function getTreeFor(array $species, Specie $parent = null): array
    {
        $result = [];

        foreach ($species as $specieName => $subspecies) {
            [$flags, $specieName] = self::splitSpecieFlagsName($specieName);

            $this->validNames[] = $specieName;

            if (self::hasIgnoreFlag($flags)) {
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

    private static function splitSpecieFlagsName(string $specie): array
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
