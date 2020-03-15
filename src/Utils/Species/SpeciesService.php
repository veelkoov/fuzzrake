<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Repository\ArtisanRepository;

class SpeciesService
{
    /**
     * @var string[]
     */
    private array $validChoices;

    /**
     * @var string[]
     */
    private array $nonsplittable;

    /**
     * @var string[]
     */
    private array $replacements;

    /**
     * @var Specie[]
     */
    private array $speciesTree;

    /**
     * @var Specie[] Associative: key = name, value = Specie object
     */
    private array $speciesFlat;

    private ArtisanRepository $artisanRepository;

    public function __construct(array $speciesData, ArtisanRepository $artisanRepository)
    {
        $this->artisanRepository = $artisanRepository;

        $this->validChoices = $this->gatherValidChoices($speciesData['valid_choices']);
        $this->replacements = $speciesData['replacements'];
        $this->nonsplittable = $speciesData['leave_unchanged'];

        $this->buildTree($speciesData['valid_choices']);
        $this->dump($this->speciesFlat);
    }

    /**
     * @return string[]
     */
    public function getValidChoices(): array
    {
        return $this->validChoices;
    }

    /**
     * @return Specie[]
     */
    public function getSpeciesFlat(): array
    {
        return $this->speciesFlat;
    }

    /**
     * @return string[]
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }

    /**
     * @return string[]
     */
    public function getNonsplittable(): array
    {
        return $this->nonsplittable;
    }

    private function gatherValidChoices(array $species): array
    {
        $result = array_keys($species);

        foreach ($species as $specie => $subspecies) {
            if (is_array($subspecies)) {
                $result = array_merge($result, $this->gatherValidChoices($subspecies));
            }
        }

        return $result;
    }

    private function buildTree(array $species): void
    {
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
    }

    public function getStats(): array
    {
        return (new StatsCalculator($this->artisanRepository->getAll(), $this->speciesFlat))->get();
    }
}
