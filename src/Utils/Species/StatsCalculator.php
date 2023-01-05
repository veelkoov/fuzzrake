<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use Psl\Vec;

class StatsCalculator
{
    /**
     * @var array<string, SpecieStats> Key = specie name
     */
    private array $result;

    /**
     * @var array<string, Specie>
     */
    private readonly array $completeList;

    /**
     * @param Artisan[]             $artisans
     * @param array<string, Specie> $completeList
     */
    public function __construct(array $artisans, array $completeList)
    {
        $this->completeList = $completeList;

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
     * @param array<string, SpecieStats> $result      Key = specie name
     * @param list<string>               $specieNames
     */
    private function appendSpeciesStats(array $result, array $specieNames, bool $does): void
    {
        foreach ($specieNames as $specieName) {
            if ($does) {
                $result[$specieName]->incDirectDoesCount();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $result[$affectedSpecieName]->incIndirectDoesCount();
                }
            } else {
                $result[$specieName]->incDirectDoesntCount();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $result[$affectedSpecieName]->incIndirectDoesntCount();
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private function getSpecieNamesAffectedInStats(string $specieName): array
    {
        if (!array_key_exists($specieName, $this->completeList)) {
            return [];
        }

        $ancestors = $this->completeList[$specieName]->getAncestors();

        return Vec\map($ancestors, fn (Specie $specie) => $specie->getName());
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
     * @param list<string> $allDoesNames
     * @param list<string> $allDoesntNames
     *
     * @return array<string, SpecieStats> Key = specie name
     */
    private function createEmptyResult(array $allDoesNames, array $allDoesntNames): array
    {
        $result = [];

        foreach (array_merge($allDoesNames, $allDoesntNames, array_keys($this->completeList)) as $specieName) {
            if (!array_key_exists($specieName, $result)) {
                $result[$specieName] = new SpecieStats($this->completeList[$specieName] ?? new Specie($specieName, true));
            }
        }

        return $result;
    }

    /**
     * @param list<Artisan> $artisans
     *
     * @return list<string>
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
