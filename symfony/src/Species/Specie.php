<?php

declare(strict_types=1);

namespace App\Species;

interface Specie
{
    public function getName(): string;

    public function getHidden(): bool;

    public function getParents(): SpecieList;

    public function getAncestors(): SpecieList;

    public function getThisAndAncestors(): SpecieList;

    public function getChildren(): SpecieList;

    public function getDescendants(): SpecieList;

    public function getThisAndDescendants(): SpecieList;

    public function getDepth(): int;
}
