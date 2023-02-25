<?php

declare(strict_types=1);

namespace App\Data\Stats\Compute;

use App\Utils\Species\Specie;

class SpeciesStatsMutable
{
    /**
     * @var array<string, SpecieStatsMutable>
     */
    private array $nameToStats = [];
    private int $unknownCount = 0;

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

    public function incUnknownCount(): void
    {
        ++$this->unknownCount;
    }

    public function getUnknownCount(): int
    {
        return $this->unknownCount;
    }
}
