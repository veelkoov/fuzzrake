<?php

declare(strict_types=1);

namespace App\Data\Stats\Compute;

use App\Data\Species\Specie;
use App\Data\Species\SpeciesList;
use App\Data\Stats\SpeciesStats;
use App\Filtering\DataRequests\Filters\SpeciesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;

class SpeciesCalculator
{
    private readonly SpeciesList $supplementedList;
    private readonly SpeciesStatsMutable $result;

    public function __construct(
        private readonly SpeciesList $completeList
    ) {
        $this->supplementedList = clone $completeList;

        $this->result = new SpeciesStatsMutable();

        foreach ($this->supplementedList->getAll() as $specie) {
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
            $speciesDoes = $artisan->getSpeciesDoes();
            $speciesDoesnt = $artisan->getSpeciesDoesnt();

            if ('' === $speciesDoes && '' === $speciesDoesnt) {
                $this->result->incUnknownCount();
                continue;
            }

            foreach (StringList::unpack($speciesDoes) as $specieName) {
                $this->result->get($this->getSpecie($specieName))->incDirectDoes();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $this->result->get($this->getSpecie($affectedSpecieName))->incIndirectDoes();
                }
            }

            foreach (StringList::unpack($speciesDoesnt) as $specieName) {
                $this->result->get($this->getSpecie($specieName))->incDirectDoesnt();

                foreach ($this->getSpecieNamesAffectedInStats($specieName) as $affectedSpecieName) {
                    $this->result->get($this->getSpecie($affectedSpecieName))->incIndirectDoesnt();
                }
            }
        }

        foreach ($this->supplementedList->getAll() as $specie) {
            if ($specie->isHidden()) {
                continue;
            }

            $filter = new SpeciesFilter([$specie->getName()], $this->completeList);

            foreach ($artisans as $artisan) {
                if ($filter->matches($artisan)) {
                    $this->result->get($specie)->incRealDoes();
                }
            }
        }

        return $this;
    }

    public function get(): SpeciesStats
    {
        return new SpeciesStats($this->result);
    }

    /**
     * @return list<string>
     */
    private function getSpecieNamesAffectedInStats(string $specieName): array
    {
        $ancestors = $this->supplementedList->getByName($specieName)->getAncestors();

        return array_map(fn (Specie $specie) => $specie->getName(), $ancestors);
    }

    private function getSpecie(string $name): Specie
    {
        $specie = $this->supplementedList->getByNameOrCreate($name, true);

        if ($specie->isHidden() && [] === $specie->getParents()) {
            $specie->addParent($this->supplementedList->getByName('Other')); // grep-species-other
        }

        return $specie;
    }
}
