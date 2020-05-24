<?php

declare(strict_types=1);

namespace App\Utils\Data\Definitions;

use App\Repository\ArtisanRepository;
use App\Utils\Species\Specie;
use TRegx\CleanRegex\Match\Details\Match;

class Species
{
    private const FLAG_PREFIX_REGEXP = '^(?<flags>[a-z]{1,2})_(?<specie>.+)$';
    private const FLAG_IGNORE_THIS_FLAG = 'i';

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

    /**
     * @param array[]|string[] $species_definitions
     */
    public function __construct(array $species_definitions, ArtisanRepository $artisanRepository)
    {
        $this->artisanRepository = $artisanRepository;
        $this->replacements = $species_definitions['replacements'];
        $this->unsplittable = $species_definitions['leave_unchanged'];

        $this->initialize($species_definitions['valid_choices']);
    }

    /**
     * @return string[]
     */
    public function getValidChoicesList()
    {
        return $this->validChoicesList;
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

    /**
     * @param array[]|string[] $species
     *
     * @return string[]
     */
    private function gatherValidChoices(array $species): array
    {
        $result = [];

        foreach ($species as $specie => $subspecies) {
            list($flags, $specie) = $this->splitSpecieFlagsName($specie);

            if (!$this->flagged($flags, self::FLAG_IGNORE_THIS_FLAG)) {
                $result[] = $specie;
            }

            if (is_array($subspecies)) {
                $result = array_merge($result, $this->gatherValidChoices($subspecies));
            }
        }

        return $result;
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

    private function initialize(array $species): void
    {
        $this->validChoicesList = [];
        $this->speciesFlat = [];
        $this->speciesTree = $this->processTreeNodes($species);

        array_unique($this->validChoicesList);
    }

    private function processTreeNodes(array $species, Specie $parent = null): array
    {
        $result = [];

        foreach ($species as $specieName => $subspecies) {
            list($flags, $specieName) = $this->splitSpecieFlagsName($specieName);

            $this->validChoicesList[] = $specieName;

            if ($this->flagged($flags, self::FLAG_IGNORE_THIS_FLAG)) {
                continue;
            }

            $result[$specieName] = $specie = $this->speciesFlat[$specieName] ??= new Specie($specieName);

            if (null !== $parent) {
                $specie->addParent($parent);
            }

            if (!empty($subspecies)) {
                foreach ($this->processTreeNodes($subspecies, $specie) as $subspecie) {
                    $specie->addChild($subspecie);
                }
            }
        }

        return $result;
    }
}
