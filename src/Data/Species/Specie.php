<?php

declare(strict_types=1);

namespace App\Data\Species;

use Stringable;

class Specie implements Stringable
{
    /**
     * @var list<Specie>
     */
    private array $parents;

    /**
     * @var list<Specie>
     */
    private array $children;

    public function __construct(
        public readonly string $name,
        public readonly bool $hidden,
        public readonly int $depth,
    ) {
    }

    /**
     * @param list<Specie> $parents
     */
    public function setParents(array $parents): void
    {
        $this->parents = $parents;
    }

    /**
     * @param list<Specie> $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getParents(): array
    {
        return $this->parents;
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getAncestors(): array
    {
        $result = $this->parents;

        foreach ($this->parents as $parent) {
            $this->addAncestorsRecursionSafely($parent, $result);
        }

        return $result;
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getSelfAndAncestors(): array
    {
        return [$this, ...$this->getAncestors()];
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getDescendants(): array
    {
        $result = $this->children;

        foreach ($this->children as $child) {
            $this->addDescendantsRecursionSafely($child, $result);
        }

        return $result;
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getSelfAndDescendants(): array
    {
        return [$this, ...$this->getDescendants()];
    }

    public function isRoot(): bool
    {
        return [] === $this->parents;
    }

    public function isLeaf(): bool
    {
        return [] === $this->children;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @param list<Specie> $result
     */
    private function addAncestorsRecursionSafely(Specie $specie, array &$result): void
    {
        foreach ($specie->parents as $parent) {
            if (!in_array($parent, $result, true)) {
                $result[] = $parent;
                $this->addAncestorsRecursionSafely($parent, $result);
            }
        }
    }

    /**
     * @param list<Specie> $result
     */
    private function addDescendantsRecursionSafely(Specie $specie, array &$result): void
    {
        foreach ($specie->children as $child) {
            if (!in_array($child, $result, true)) {
                $result[] = $child;
                $this->addDescendantsRecursionSafely($child, $result);
            }
        }
    }
}
