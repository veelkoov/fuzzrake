<?php

declare(strict_types=1);

namespace App\Data\Species\Stats;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<string, SpecieStats>
 */
readonly class SpeciesStats implements IteratorAggregate
{
    /**
     * @var array<string, SpecieStats>
     */
    private array $nameToStats;
    public int $unknownCount;

    public function __construct(MutableSpeciesStats $source)
    {
        $nameToStats = [];

        foreach ($source->getAll() as $specieStats) {
            $nameToStats[$specieStats->specie->name] = new SpecieStats($specieStats);
        }

        $this->unknownCount = $source->getUnknownCount();
        $this->nameToStats = $nameToStats;
    }

    public function get(string $name): ?SpecieStats
    {
        return $this->nameToStats[$name] ?? null;
    }

    /**
     * @return Traversable<string, SpecieStats>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->nameToStats);
    }
}
