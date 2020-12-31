<?php

declare(strict_types=1);

namespace App\Utils\Species;

use InvalidArgumentException;

class Specie
{
    private string $name;

    /**
     * @var Specie[]
     */
    private array $parents = [];

    /**
     * @var Specie[]
     */
    private array $children = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return Specie[]
     */
    public function getParents(): array
    {
        return $this->parents;
    }

    public function addParent(Specie $parent): Specie
    {
        if ($parent === $this) {
            throw new InvalidArgumentException("Recursion in specie: {$this->name}");
        }

        $this->parents[] = $parent;

        return $this;
    }

    /**
     * @return Specie[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(Specie $child): Specie
    {
        if ($child === $this) {
            throw new InvalidArgumentException("Recursion in specie: {$this->name}");
        }
        $this->children[] = $child;

        return $this;
    }

    /**
     * @return Specie[]
     */
    public function getAncestors(): array
    {
        $result = $this->parents;

        foreach ($this->parents as $parent) {
            $this->getAncestorsRecursionSafely($parent, $result);
        }

        return $result;
    }

    public function isDescendantOf(Specie $ancestor): bool
    {
        return in_array($ancestor, $this->getAncestors());
    }

    /**
     * @return Specie[]
     */
    public function getDescendants(): array
    {
        $result = $this->children;

        foreach ($this->children as $child) {
            $this->getDescendantsRecursionSafely($child, $result);
        }

        return $result;
    }

    public function isRoot(): bool
    {
        return empty($this->parents);
    }

    public function isLeaf(): bool
    {
        return empty($this->children);
    }

    public function hasChildren(): bool
    {
        return !$this->isLeaf();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Specie[] $result
     */
    private function getAncestorsRecursionSafely(Specie $specie, array &$result): void
    {
        foreach ($specie->getParents() as $parent) {
            if ($parent === $this) {
                throw new InvalidArgumentException("Recursion in specie: {$this->name}");
            }

            if (!in_array($parent, $result)) {
                $result[] = $parent;
                $this->getAncestorsRecursionSafely($parent, $result);
            }
        }
    }

    /**
     * @param Specie[] $result
     */
    private function getDescendantsRecursionSafely(Specie $specie, array &$result): void
    {
        foreach ($specie->getChildren() as $child) {
            if ($child === $this) {
                throw new InvalidArgumentException("Recursion in specie: {$this->name}");
            }

            if (!in_array($child, $result)) {
                $result[] = $child;
                $this->getDescendantsRecursionSafely($child, $result);
            }
        }
    }
}
