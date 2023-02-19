<?php

declare(strict_types=1);

namespace App\Data\Stats;

use App\Utils\Species\Specie;

class SpeciesStatsMutable
{
    /**
     * @var array<string, SpecieStatsMutable>
     */
    private array $nameToStats;

    public function get(Specie $specie): SpecieStatsMutable
    {
        return $this->nameToStats[$specie->getName()] ??= new SpecieStatsMutable($specie);
    }

    /**
     * @return list<SpecieStatsMutable>
     */
    public function getAll(): array
    {
        return array_values($this->nameToStats);
    }
}
