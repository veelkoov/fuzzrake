<?php

declare(strict_types=1);

namespace App\Data\Stats;

readonly class SpecieStats
{
    public int $directDoesCount;
    public int $directDoesntCount;
    public int $directTotalCount;
    public int $indirectDoesCount;
    public int $indirectDoesntCount;
    public int $indirectTotalCount;
    public int $totalDoesCount;
    public int $totalDoesntCount;
    public int $totalCount;

    public function __construct(
        SpecieStatsMutable $source,
    ) {
        $this->directDoesCount = $source->getDirectDoesCount();
        $this->directDoesntCount = $source->getDirectDoesntCount();
        $this->directTotalCount = $source->getDirectTotalCount();
        $this->indirectDoesCount = $source->getIndirectDoesCount();
        $this->indirectDoesntCount = $source->getIndirectDoesntCount();
        $this->indirectTotalCount = $source->getIndirectTotalCount();
        $this->totalDoesCount = $source->getTotalDoesCount();
        $this->totalDoesntCount = $source->getTotalDoesntCount();
        $this->totalCount = $source->getTotalCount();
    }
}
