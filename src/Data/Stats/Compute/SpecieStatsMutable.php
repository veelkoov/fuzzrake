<?php

declare(strict_types=1);

namespace App\Data\Stats\Compute;

use App\Utils\Species\Specie;

class SpecieStatsMutable
{
    private int $directDoes = 0;
    private int $directDoesnt = 0;
    private int $directTotal = 0;
    private int $indirectDoes = 0;
    private int $indirectDoesnt = 0;
    private int $indirectTotal = 0;
    private int $totalDoes = 0;
    private int $totalDoesnt = 0;
    private int $total = 0;

    public function __construct(
        public readonly Specie $specie,
    ) {
    }

    public function incDirectDoesCount(): void
    {
        ++$this->directDoes;
        ++$this->directTotal;
        ++$this->totalDoes;
        ++$this->total;
    }

    public function incDirectDoesntCount(): void
    {
        ++$this->directDoesnt;
        ++$this->directTotal;
        ++$this->totalDoesnt;
        ++$this->total;
    }

    public function incIndirectDoesCount(): void
    {
        ++$this->indirectDoes;
        ++$this->indirectTotal;
        ++$this->totalDoes;
        ++$this->total;
    }

    public function incIndirectDoesntCount(): void
    {
        ++$this->indirectDoesnt;
        ++$this->indirectTotal;
        ++$this->totalDoesnt;
        ++$this->total;
    }

    public function getSpecie(): Specie
    {
        return $this->specie;
    }

    public function getDirectDoes(): int
    {
        return $this->directDoes;
    }

    public function getDirectDoesnt(): int
    {
        return $this->directDoesnt;
    }

    public function getDirectTotal(): int
    {
        return $this->directTotal;
    }

    public function getIndirectDoes(): int
    {
        return $this->indirectDoes;
    }

    public function getIndirectDoesnt(): int
    {
        return $this->indirectDoesnt;
    }

    public function getIndirectTotal(): int
    {
        return $this->indirectTotal;
    }

    public function getTotalDoes(): int
    {
        return $this->totalDoes;
    }

    public function getTotalDoesnt(): int
    {
        return $this->totalDoesnt;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
