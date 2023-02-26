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

    private int $realDoes = 0;

    public function __construct(
        public readonly Specie $specie,
    ) {
    }

    public function incDirectDoes(): void
    {
        ++$this->directDoes;
        ++$this->directTotal;
        ++$this->totalDoes;
        ++$this->total;
    }

    public function incDirectDoesnt(): void
    {
        ++$this->directDoesnt;
        ++$this->directTotal;
        ++$this->totalDoesnt;
        ++$this->total;
    }

    public function incIndirectDoes(): void
    {
        ++$this->indirectDoes;
        ++$this->indirectTotal;
        ++$this->totalDoes;
        ++$this->total;
    }

    public function incIndirectDoesnt(): void
    {
        ++$this->indirectDoesnt;
        ++$this->indirectTotal;
        ++$this->totalDoesnt;
        ++$this->total;
    }

    public function incRealDoes(): void
    {
        ++$this->realDoes;
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

    public function getRealDoes(): int
    {
        return $this->realDoes;
    }
}
