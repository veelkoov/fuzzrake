<?php

declare(strict_types=1);

namespace App\Data\Stats;

readonly class SpeciesStats
{
    /**
     * @var array<string, SpecieStats>
     */
    private array $nameToStats;

    public function __construct(SpeciesStatsMutable $source)
    {
        $nameToStats = [];

        foreach ($source->getAll() as $specieStats) {
            $nameToStats[$specieStats->specie->getName()] = new SpecieStats($specieStats);
        }

        $this->nameToStats = $nameToStats;
    }

    public function get(string $name): ?SpecieStats
    {
        return $this->nameToStats[$name] ?? null;
    }
}
