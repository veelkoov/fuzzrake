<?php

declare(strict_types=1);

namespace App\Data\Species;

use InvalidArgumentException;
use Stringable;

class Specie implements Stringable
{
    /**
     * @var list<Specie>
     */
    private array $parents = [];

    /**
     * @var list<Specie>
     */
    private array $children = [];

    private int $depth = 0;

    public function __construct(
        private readonly string $name,
        private bool $hidden,
    ) {
    }

    /**
     * @return list<Specie>
     */
    public function getParents(): array
    {
        return $this->parents;
    }

    public function addParent(Specie $parent): void
    {
        if (in_array($this, $parent->getSelfAndAncestors(), true)) {
            throw new InvalidArgumentException("Recursion in specie: $this->name");
        }

        if (!in_array($parent, $this->parents, true)) {
            $this->parents[] = $parent;

            if ($parent->depth >= $this->depth) {
                $this->depth = $parent->depth + 1;
            }
        }
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @return Specie[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(Specie $child): void
    {
        if (in_array($this, $child->getSelfAndDescendants(), true)) {
            throw new InvalidArgumentException("Recursion in specie: $this->name");
        }

        if (!in_array($child, $this->children, true)) {
            $this->children[] = $child;
        }
    }

    /**
     * @return list<Specie>
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
     * @return list<Specie>
     */
    public function getSelfAndAncestors(): array
    {
        return [$this, ...$this->getAncestors()];
    }

    /**
     * @return list<Specie>
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
     * @return list<Specie>
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param list<Specie> $result
     */
    private function addAncestorsRecursionSafely(Specie $specie, array &$result): void
    {
        foreach ($specie->getParents() as $parent) {
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
        foreach ($specie->getChildren() as $child) {
            if (!in_array($child, $result, true)) {
                $result[] = $child;
                $this->addDescendantsRecursionSafely($child, $result);
            }
        }
    }

    public function addParentTwoWay(Specie $parent): void
    {
        $this->addParent($parent);
        $parent->addChild($this);
    }
}
