<?php

declare(strict_types=1);

namespace App\Data\Stats\Compute;

use App\Data\Stats\SpeciesStats;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Species\Specie;
use App\Utils\Species\SpeciesList;
use App\Utils\StringList;

class SpeciesCalculator
{
    private readonly SpeciesList $completeList;
    private readonly SpeciesStatsMutable $result;

    public function __construct(SpeciesList $completeList)
    {
        $this->completeList = clone $completeList;

        $this->result = new SpeciesStatsMutable();

        foreach ($this->completeList->getAll() as $specie) {
            $this->result->get($specie);
        }
    }

    public static function for(SpeciesList $completeList): self
    {
        return new self($completeList);
    }

    /**
     * @param list<Artisan> $artisans
     */
    public function add(array $artisans): self
    {
        foreach ($artisans as $artisan) {
            if ('' === $artisan->getSpeciesDoes() && '' === $artisan->getSpeciesDoesnt()) {
                $this->result->incUnknownCount();
                continue;
            }

            foreach (StringList::unpack($artisan->getSpeciesDoes()) as $specieName) {
                $this->result->get($this->getSpecie($specieName))->incDirectDoesCount();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $this->result->get($this->getSpecie($affectedSpecieName))->incIndirectDoesCount();
                }
            }

            foreach (StringList::unpack($artisan->getSpeciesDoesnt()) as $specieName) {
                $this->result->get($this->getSpecie($specieName))->incDirectDoesntCount();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $this->result->get($this->getSpecie($affectedSpecieName))->incIndirectDoesntCount();
                }
            }
        }

        return $this;
    }

    public function get(): SpeciesStats // FIXME: Invalid calculations
    {
        return new SpeciesStats($this->result);
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

        return array_map(fn (Specie $specie) => $specie->getName(), $ancestors);
    }

    private function getSpecie(string $name): Specie
    {
        return $this->completeList->getByNameOrCreate($name, true);
    }
}
