<?php

declare(strict_types=1);

namespace App\Data\Stats;

use App\Data\Definitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Species\Specie;
use App\Utils\Species\SpeciesList;
use App\Utils\StringList;
use Psl\Vec;

class SpeciesCalculator
{
    private readonly SpeciesList $completeList;
    private readonly SpeciesStatsMutable $result;

    public function __construct(SpeciesList $completeList)
    {
        $this->completeList = clone $completeList;

        $this->result = new SpeciesStatsMutable();
    }

    public static function for(SpeciesList $completeList): self
    {
        return new self($completeList);
    }

    /**
     * @param Artisan[] $artisans
     */
    public function add(array $artisans): self
    {
        $allDoesNames = self::extractSpecieNamesWithRepetitions($artisans, Field::SPECIES_DOES);
        $this->appendSpeciesStats(true, $allDoesNames);

        $allDoesntNames = self::extractSpecieNamesWithRepetitions($artisans, Field::SPECIES_DOESNT);
        $this->appendSpeciesStats(false, $allDoesntNames);

        if ([] === $allDoesNames && [] === $allDoesntNames) {
            $this->result->incUnknownCount(); // FIXME: Doesn't work
        }

// TODO
//        uasort($this->result, fn (SpecieStatsMutable $a, SpecieStatsMutable $b) => self::compare($a, $b));

        return $this;
    }

    public function get(): SpeciesStats // FIXME: Invalid calculations
    {
        return new SpeciesStats($this->result);
    }

    /**
     * @param list<string> $specieNames
     */
    private function appendSpeciesStats(bool $does, array $specieNames): void
    {
        foreach ($specieNames as $specieName) {
            if ($does) {
                $this->result->get($this->getSpecie($specieName))->incDirectDoesCount();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $this->result->get($this->getSpecie($affectedSpecieName))->incIndirectDoesCount();
                }
            } else {
                $this->result->get($this->getSpecie($specieName))->incDirectDoesntCount();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $this->result->get($this->getSpecie($affectedSpecieName))->incIndirectDoesntCount();
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private function getSpecieNamesAffectedInStats(string $specieName): array
    {
        if (!$this->completeList->hasName($specieName)) {
            return [];
        }

        $ancestors = $this->completeList->getByName($specieName)->getAncestors();

        return Vec\map($ancestors, fn (Specie $specie) => $specie->getName());
    }

// TODO
//    private static function compare(SpecieStatsMutable $a, SpecieStatsMutable $b): int
//    {
//        $res = $b->getTotalCount() - $a->getTotalCount();
//
//        if (0 !== $res) {
//            return $res;
//        }
//
//        return $b->getDirectTotalCount() - $a->getDirectTotalCount();
//    }

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

    private function getSpecie(string $name): Specie
    {
        return $this->completeList->getByNameOrCreate($name, true);
    }
}
