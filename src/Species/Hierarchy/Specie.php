<?php

declare(strict_types=1);

namespace App\Species\Hierarchy;

interface Specie
{
    public function getName(): string;

    public function getHidden(): bool;

    public function getParents(): SpecieSet;

    public function getAncestors(): SpecieSet;

    public function getThisAndAncestors(): SpecieSet;

    public function getChildren(): SpecieSet;

    public function getDescendants(): SpecieSet;

    public function getThisAndDescendants(): SpecieSet;

    public function getDepth(): int;
}
