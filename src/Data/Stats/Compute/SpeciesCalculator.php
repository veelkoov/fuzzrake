<?php

declare(strict_types=1);

namespace App\Data\Stats\Compute;

use App\Data\Stats\SpeciesStats;
use App\Filtering\DataRequests\Filters\SpeciesSearchResolver;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Species\Specie;
use App\Utils\Species\SpeciesList;
use App\Utils\StringList;

class SpeciesCalculator
{
    private readonly SpeciesList $completeList;
    private readonly SpeciesStatsMutable $result;
    private readonly SpeciesSearchResolver $resolver;

    public function __construct(SpeciesList $completeList)
    {
        $this->completeList = clone $completeList;
        $this->resolver = new SpeciesSearchResolver($this->completeList);

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

            $resolvedDoes = $this->resolver->resolveDoes($speciesDoes, $speciesDoesnt);
            foreach ($resolvedDoes as $specieName) {
                $this->result->get($this->getSpecie($specieName))->incRealDoes();
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
        if (!$this->completeList->hasName($specieName)) {
            return [];
        }

        $ancestors = $this->completeList->getByName($specieName)->getAncestors();

        return array_map(fn (Specie $specie) => $specie->getName(), $ancestors);
    }

    private function getSpecie(string $name): Specie
    {
        $specie = $this->completeList->getByNameOrCreate($name, true);

        if ($specie->isHidden() && [] === $specie->getParents()) {
            $specie->addParent($this->completeList->getByName('Other')); // grep-species-other
        }

        return $specie;
    }
}
