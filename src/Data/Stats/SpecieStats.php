<?php

declare(strict_types=1);

namespace App\Data\Stats;

use App\Data\Species\Specie;
use App\Data\Stats\Compute\SpecieStatsMutable;

readonly class SpecieStats
{
    public Specie $specie;

    public int $directDoes;
    public int $directDoesnt;
    public int $directTotal;

    public int $indirectDoes;
    public int $indirectDoesnt;
    public int $indirectTotal;

    public int $totalDoes;
    public int $totalDoesnt;
    public int $total;

    public int $realDoes;

    public function __construct(
        SpecieStatsMutable $source,
    ) {
        $this->specie = $source->specie;

        $this->directDoes = $source->getDirectDoes();
        $this->directDoesnt = $source->getDirectDoesnt();
        $this->directTotal = $source->getDirectTotal();

        $this->indirectDoes = $source->getIndirectDoes();
        $this->indirectDoesnt = $source->getIndirectDoesnt();
        $this->indirectTotal = $source->getIndirectTotal();

        $this->totalDoes = $source->getTotalDoes();
        $this->totalDoesnt = $source->getTotalDoesnt();
        $this->total = $source->getTotal();

        $this->realDoes = $source->getRealDoes();
    }
}
