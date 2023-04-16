<?php

declare(strict_types=1);

namespace App\Data\Species\Stats;

use App\Data\Species\Specie;

class MutableSpeciesStats
{
    /**
     * @var array<string, MutableSpecieStats>
     */
    private array $nameToStats = [];
    private int $unknownCount = 0;

    public function get(Specie $specie): MutableSpecieStats
    {
        return $this->nameToStats[$specie->name] ??= new MutableSpecieStats($specie);
    }

    /**
     * @return MutableSpecieStats[]
     *
     * @phpstan-return list<MutableSpecieStats>
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
