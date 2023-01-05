<?php

declare(strict_types=1);

namespace App\Utils\Species;

use InvalidArgumentException;
use Stringable;

class Specie implements Stringable
{
    /**
     * @var Specie[]
     */
    private array $parents = [];

    /**
     * @var Specie[]
     */
    private array $children = [];

    public function __construct(
        private readonly string $name,
        private bool $hidden,
    ) {
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
            throw new InvalidArgumentException("Recursion in specie: $this->name");
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
            throw new InvalidArgumentException("Recursion in specie: $this->name");
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
            $this->addAncestorsRecursionSafely($parent, $result);
        }

        return $result;
    }

    public function isDescendantOf(Specie $ancestor): bool
    {
        return in_array($ancestor, $this->getAncestors(), true);
    }

    /**
     * @return Specie[]
     */
    public function getDescendants(): array
    {
        $result = $this->children;

        foreach ($this->children as $child) {
            $this->addDescendantsRecursionSafely($child, $result);
        }

        return $result;
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
     * @param Specie[] $result
     */
    private function addAncestorsRecursionSafely(Specie $specie, array &$result): void
    {
        foreach ($specie->getParents() as $parent) {
            if ($parent === $this) {
                throw new InvalidArgumentException("Recursion in specie: $this->name");
            }

            if (!in_array($parent, $result, true)) {
                $result[] = $parent;
                $this->addAncestorsRecursionSafely($parent, $result);
            }
        }
    }

    /**
     * @param Specie[] $result
     */
    private function addDescendantsRecursionSafely(Specie $specie, array &$result): void
    {
        foreach ($specie->getChildren() as $child) {
            if ($child === $this) {
                throw new InvalidArgumentException("Recursion in specie: $this->name");
            }

            if (!in_array($child, $result, true)) {
                $result[] = $child;
                $this->addDescendantsRecursionSafely($child, $result);
            }
        }
    }
}
