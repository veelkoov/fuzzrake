<?php

declare(strict_types=1);

namespace App\Data\Stats;

use App\Utils\Species\Specie;

class SpecieStatsMutable
{
    private int $directDoesCount = 0;
    private int $directDoesntCount = 0;
    private int $directTotalCount = 0;
    private int $indirectDoesCount = 0;
    private int $indirectDoesntCount = 0;
    private int $indirectTotalCount = 0;
    private int $totalDoesCount = 0;
    private int $totalDoesntCount = 0;
    private int $totalCount = 0;

    public function __construct(
        public readonly Specie $specie,
    ) {
    }

    public function incDirectDoesCount(): void
    {
        ++$this->directDoesCount;
        ++$this->directTotalCount;
        ++$this->totalDoesCount;
        ++$this->totalCount;
    }

    public function incDirectDoesntCount(): void
    {
        ++$this->directDoesntCount;
        ++$this->directTotalCount;
        ++$this->totalDoesntCount;
        ++$this->totalCount;
    }

    public function incIndirectDoesCount(): void
    {
        ++$this->indirectDoesCount;
        ++$this->indirectTotalCount;
        ++$this->totalDoesCount;
        ++$this->totalCount;
    }

    public function incIndirectDoesntCount(): void
    {
        ++$this->indirectDoesntCount;
        ++$this->indirectTotalCount;
        ++$this->totalDoesntCount;
        ++$this->totalCount;
    }

    public function getSpecie(): Specie
    {
        return $this->specie;
    }

    public function getDirectDoesCount(): int
    {
        return $this->directDoesCount;
    }

    public function getDirectDoesntCount(): int
    {
        return $this->directDoesntCount;
    }

    public function getDirectTotalCount(): int
    {
        return $this->directTotalCount;
    }

    public function getIndirectDoesCount(): int
    {
        return $this->indirectDoesCount;
    }

    public function getIndirectDoesntCount(): int
    {
        return $this->indirectDoesntCount;
    }

    public function getIndirectTotalCount(): int
    {
        return $this->indirectTotalCount;
    }

    public function getTotalDoesCount(): int
    {
        return $this->totalDoesCount;
    }

    public function getTotalDoesntCount(): int
    {
        return $this->totalDoesntCount;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
