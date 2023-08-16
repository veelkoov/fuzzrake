<?php

declare(strict_types=1);

namespace App\Data\Species;

readonly class Species
{
    /**
     * @param list<Specie> $tree
     */
    public function __construct(
        public SpeciesList $list,
        public array $tree,
    ) {
    }
}
