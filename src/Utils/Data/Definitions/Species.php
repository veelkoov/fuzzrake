<?php

declare(strict_types=1);

namespace App\Utils\Data\Definitions;

use App\Repository\ArtisanRepository;
use App\Utils\Species\Specie;
use App\Utils\Species\StatsCalculator;
use TRegx\CleanRegex\Match\Details\Match;

class Species
{
    private const META_INFO_PREFIX_REGEXP = '^(?<flags>[a-z]{1,2})_(?<specie>.+)$';

    private const FLAG_IGNORE_THIS_FLAG = 'i';
    private const FLAG_SKIP_THIS_AND_CHILD_IN_FILTERS = 'f'; // TODO

    /**
     * @return string[]
     */
    private array $validChoicesList;

    /**
     * @return string[]
     */
    private array $replacements;

    /**
     * @return string[]
     */
    private array $unsplittable;

    /**
     * @var Specie[]
     */
    private array $speciesTree;

    /**
     * @var Specie[] Associative: key = name, value = Specie object
     */
    private array $speciesFlat;

    private ArtisanRepository $artisanRepository;

    /**
     * @param array[]|string[] $species_definitions
     */
    public function __construct(array $species_definitions, ArtisanRepository $artisanRepository)
    {
        $this->artisanRepository = $artisanRepository;
        $this->validChoicesList = $this->gatherValidChoices($species_definitions['valid_choices']);
        $this->replacements = $species_definitions['replacements'];
        $this->unsplittable = $species_definitions['leave_unchanged'];

        $this->buildTree($species_definitions['valid_choices']);
        $this->dump($this->speciesFlat);
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
        return pattern(self::META_INFO_PREFIX_REGEXP)->match($specie)->findFirst(function (Match $match): array {
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

    private function buildTree(array $species): void
    {
        $this->speciesFlat = [];
        $this->speciesTree = $this->processTreeNodes($species);
    }

    private function processTreeNodes(array $species, Specie $parent = null): array
    {
        $result = [];

        foreach ($species as $name => $subspecies) {
            $result[$name] = $specie = $this->speciesFlat[$name] ??= new Specie($name);

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

    /**
     * @param Specie[] $species
     */
    private function dump(array $species): void
    {
        // TODO: Get rid of
    }

    public function getStats(): array
    {
        return (new StatsCalculator($this->artisanRepository->getAll(), $this->speciesFlat))->get();
    }
}
