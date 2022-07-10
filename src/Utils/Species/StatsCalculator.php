<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;

class StatsCalculator
{
    /**
     * @var SpecieStats[] 'Specie name' => SpecieStats
     */
    private array $result;

    /**
     * @var Specie[]
     */
    private readonly array $speciesFlat;

    /**
     * @param Artisan[] $artisans
     * @param Specie[]  $speciesFlat
     */
    public function __construct(array $artisans, array $speciesFlat)
    {
        $this->speciesFlat = $speciesFlat;

        $allDoesNames = self::extractSpecieNamesWithRepetitions($artisans, Field::SPECIES_DOES);
        $allDoesntNames = self::extractSpecieNamesWithRepetitions($artisans, Field::SPECIES_DOESNT);

        $this->result = self::createEmptyResult($allDoesNames, $allDoesntNames);

        $this->appendSpeciesStats($this->result, $allDoesNames, true);
        $this->appendSpeciesStats($this->result, $allDoesntNames, false);

        uasort($this->result, fn (SpecieStats $a, SpecieStats $b) => self::compare($a, $b));
    }

    /**
     * @return SpecieStats[] 'Specie name' => SpecieStats
     */
    public function get(): array
    {
        return $this->result;
    }

    /**
     * @param SpecieStats[] $result  'Specie name' => SpecieStats
     * @param string[]      $species
     */
    private function appendSpeciesStats(array $result, array $species, bool $does): void
    {
        foreach ($species as $specie) {
            if ($does) {
                $result[$specie]->incDirectDoesCount();

                foreach ($this->getSpeciesAffectedInStats($specie) as $affectedSpecie) {
                    $result[$affectedSpecie]->incIndirectDoesCount();
                }
            } else {
                $result[$specie]->incDirectDoesntCount();

                foreach ($this->getSpeciesAffectedInStats($specie) as $affectedSpecie) {
                    $result[$affectedSpecie]->incIndirectDoesntCount();
                }
            }
        }
    }

    /**
     * @return string[]
     */
    private function getSpeciesAffectedInStats(string $specieName): array
    {
        if (!array_key_exists($specieName, $this->speciesFlat)) {
            return [];
        }

        return array_map(fn (Specie $specie): string => $specie->getName(), $this->speciesFlat[$specieName]->getAncestors());
    }

    private static function compare(SpecieStats $a, SpecieStats $b): int
    {
        $res = $b->getTotalCount() - $a->getTotalCount();

        if (0 !== $res) {
            return $res;
        }

        return $b->getDirectTotalCount() - $a->getDirectTotalCount();
    }

    /**
     * @param string[] $allDoesNames
     * @param string[] $allDoesntNames
     *
     * @return SpecieStats[] 'Specie name' => SpecieStats
     */
    private function createEmptyResult(array $allDoesNames, array $allDoesntNames): array
    {
        $result = [];

        foreach (array_merge($allDoesNames, $allDoesntNames, array_keys($this->speciesFlat)) as $specieName) {
            if (!array_key_exists($specieName, $result)) {
                $result[$specieName] = new SpecieStats($this->speciesFlat[$specieName] ?? new Specie($specieName, true));
            }
        }

        return $result;
    }

    /**
     * @param Artisan[] $artisans
     *
     * @return string[]
     */
    private static function extractSpecieNamesWithRepetitions(array $artisans, Field $field): array
    {
        $result = [];

        foreach ($artisans as $artisan) {
            array_push($result, ...StringList::unpack($artisan->getString($field)));
        }

        return $result;
    }
}
