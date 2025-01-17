<?php

declare(strict_types=1);

namespace App\Species;

interface Specie
{
    public function getName(): string;

    public function getHidden(): bool;

    /**
     * @return list<Specie>
     */
    public function getParents(): array;

    /**
     * @return list<Specie>
     */
    public function getAncestors(): array;

    /**
     * @return list<Specie>
     */
    public function getThisAndAncestors(): array;

    /**
     * @return list<Specie>
     */
    public function getChildren(): array;

    /**
     * @return list<Specie>
     */
    public function getDescendants(): array;

    /**
     * @return list<Specie>
     */
    public function getThisAndDescendants(): array;
}
