<?php

declare(strict_types=1);

namespace App\Data\Species;

class MutableSpecies
{
    public readonly MutableSpeciesList $list;

    /**
     * @var MutableSpecie[]
     *
     * @phpstan-var list<MutableSpecie>
     */
    public array $tree = [];

    public function __construct()
    {
        $this->list = new MutableSpeciesList();
    }
}
