<?php

declare(strict_types=1);

namespace App\Data\Species\Stats;

use App\Data\Species\Specie;
use App\Data\Species\SpeciesList;
use App\Filtering\DataRequests\Filters\SpeciesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;

class SpeciesStatsBuilder
{
    private readonly MutableSpeciesStats $result;

    public function __construct(
        private readonly SpeciesList $completeList
    ) {
        $this->result = new MutableSpeciesStats();
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
                $specie = $this->getSpecie($specieName);
                $this->result->get($specie)->incDirectDoes();

                foreach ($specie->getAncestors() as $ancestor) {
                    $this->result->get($ancestor)->incIndirectDoes();
                }
            }

            foreach (StringList::unpack($speciesDoesnt) as $specieName) {
                $specie = $this->getSpecie($specieName);
                $this->result->get($specie)->incDirectDoesnt();

                foreach ($specie->getAncestors() as $ancestor) {
                    $this->result->get($ancestor)->incIndirectDoesnt();
                }
            }
        }

        foreach ($this->completeList->items as $specie) {
            if ($specie->hidden) {
                continue;
            }

            $filter = new SpeciesFilter([$specie->name], $this->completeList);

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

    private function getSpecie(string $name): Specie
    {
        if ($this->completeList->hasName($name)) {
            return $this->completeList->getByName($name);
        } else {
            $result = new Specie($name, true, 1);
            $result->setParents([$this->completeList->getByName('Other')]); // grep-species-other

            return $result;
        }
    }
}
