<?php

declare(strict_types=1);

namespace App\Data\Species\Stats;

use App\Data\Species\Specie;
use App\Data\Species\SpeciesList;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;

final class SpeciesStatsBuilder
{
    private readonly MutableSpeciesStats $result;

    /**
     * @param list<Artisan> $artisans
     */
    public static function for(SpeciesList $completeList, array $artisans): SpeciesStats
    {
        return (new self($completeList, $artisans))->get();
    }

    /**
     * @param list<Artisan> $artisans
     */
    private function __construct(
        private readonly SpeciesList $completeList,
        array $artisans,
    ) {
        $this->result = new MutableSpeciesStats();

        $this->add($artisans);
    }

    private function get(): SpeciesStats
    {
        return new SpeciesStats($this->result);
    }

    /**
     * @param list<Artisan> $artisans
     */
    private function add(array $artisans): void
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
