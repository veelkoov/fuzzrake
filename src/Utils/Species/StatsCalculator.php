<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Entity\Artisan;
use App\Utils\StringList;

class StatsCalculator
{
    private array $result = [];

    /**
     * @var Specie[]
     */
    private array $speciesFlat;

    /**
     * @var string[]
     */
    private array $speciesDoesnt;

    /**
     * @var string[]
     */
    private array $speciesDoes;

    /**
     * @var Artisan[]
     */
    private array $artisans;

    public function __construct(array $artisans, array $speciesFlat)
    {
        $this->speciesFlat = $speciesFlat;
        $this->artisans = $artisans;

        $this->InitializeSpeciesDoesAndNot();
        $this->initializeEmptyResult();

        foreach ($artisans as $artisan) {
            $this->appendSpeciesStats($this->result, StringList::unpack($artisan->getSpeciesDoes()), true);
            $this->appendSpeciesStats($this->result, StringList::unpack($artisan->getSpeciesDoesnt()), false);
        }

        uasort($this->result, function (array $a, array $b): int {
            return $b['directTotalCount'] - $a['directTotalCount'];
        });
    }

    public function get(): array
    {
        return $this->result;
    }

    /**
     * @param string[] $species
     */
    private function appendSpeciesStats(array &$result, array $species, bool $does): void
    {
        foreach ($species as $specie) {
            ++$result[$specie][$does ? 'directDoesCount' : 'directDoesntCount'];
            ++$result[$specie]['directTotalCount'];

            foreach ($this->getSpeciesAffectedInStats($specie) as $affectedSpecie) {
                ++$result[$affectedSpecie][$does ? 'indirectDoesCount' : 'indirectDoesntCount'];
                ++$result[$affectedSpecie]['indirectTotalCount'];
            }
        }
    }

    /**
     * @return string[]
     */
    private function getSpeciesAffectedInStats(string $specieName): array
    {
        return array_map(function (Specie $specie): string { return $specie->getName(); }, array_merge(
            $this->speciesFlat[$specieName]->getAncestors()
        ));
    }

    private function InitializeSpeciesDoesAndNot(): void
    {
        $this->speciesDoes = array_map(function (Artisan $artisan): array {
            return StringList::unpack($artisan->getSpeciesDoes());
        }, $this->artisans);

        $this->speciesDoesnt = array_map(function (Artisan $artisan): array {
            return StringList::unpack($artisan->getSpeciesDoesnt());
        }, $this->artisans);
    }

    private function initializeEmptyResult()
    {
        foreach (array_merge($this->speciesDoes, $this->speciesDoesnt, [array_keys($this->speciesFlat)]) as $species) {
            foreach ($species as $specie) {
                $this->result[$specie] = [
                    'directDoesCount'     => 0,
                    'directDoesntCount'   => 0,
                    'directTotalCount'    => 0,
                    'indirectDoesCount'   => 0,
                    'indirectDoesntCount' => 0,
                    'indirectTotalCount'  => 0,
                ];
            }
        }
    }
}
