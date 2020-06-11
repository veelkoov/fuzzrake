<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Repository\ArtisanRepository;
use TRegx\CleanRegex\Match\Details\Match;

class Species
{
    private const FLAG_PREFIX_REGEXP = '^(?<flags>[a-z]{1,2})_(?<specie>.+)$';
    private const FLAG_IGNORE_THIS_FLAG = 'i'; // Marks species considered valid, but which won't e.g. be available for filtering

    /**
     * @return string[]
     */
    private array $replacements;

    /**
     * @return string[]
     */
    private array $unsplittable;

    /**
     * @var Specie[] Associative: key = name, value = Specie object. Species fit for filtering
     */
    private array $speciesFlat;

    /**
     * @var Specie[] Species fit for filtering
     */
    private array $speciesTree;

    /**
     * @var string[] Names of species considered valid by the validator (list of all, not only fit for filtering)
     */
    private array $validChoicesList;

    private ArtisanRepository $artisanRepository;

    public function __construct(array $speciesDefinitions, ArtisanRepository $artisanRepository)
    {
        $this->artisanRepository = $artisanRepository;
        $this->replacements = $speciesDefinitions['replacements'];
        $this->unsplittable = $speciesDefinitions['leave_unchanged'];

        $this->initialize($speciesDefinitions['valid_choices']);
    }

    /**
     * @return string[]
     */
    public function getValidChoicesList()
    {
        return $this->validChoicesList;
    }

    /**
     * @return Specie[]
     */
    public function getSpeciesFlat(): array
    {
        return $this->speciesFlat;
    }

    /**
     * @return Specie[]
     */
    public function getSpeciesTree(): array
    {
        return $this->speciesTree;
    }

    /**
     * @return string[]
     */
    public function getListFixerReplacements(): array
    {
        return $this->replacements;
    }

    /**
     * @return string[]
     */
    public function getListFixerUnsplittable(): array
    {
        return $this->unsplittable;
    }

    private function splitSpecieFlagsName(string $specie): array
    {
        return pattern(self::FLAG_PREFIX_REGEXP)->match($specie)->findFirst(function (Match $match): array {
            return [$match->group('flags')->text(), $match->group('specie')->text()];
        })->orReturn(['', $specie]);
    }

    private function flagged(string $flags, string $flag): bool
    {
        return false !== strpos($flags, $flag);
    }

    private function initialize(array $species): void
    {
        $this->speciesFlat = [];
        $this->speciesTree = $this->getTreeFor($species);

        $this->validChoicesList = $this->gatherValidChoices($species);
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
            list(, $specie) = $this->splitSpecieFlagsName($specie);

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
            list($flags, $specieName) = $this->splitSpecieFlagsName($specieName);

            $this->validChoicesList[] = $specieName;

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
        $specie = $this->getSpecie($specieName);

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

    private function getSpecie($specieName): Specie
    {
        return $this->speciesFlat[$specieName] ??= new Specie($specieName);
    }
}
