<?php

declare(strict_types=1);

namespace App\Data\Species;

use App\Data\Species\Exceptions\RecursionSpecieException;
use Stringable;

class MutableSpecie implements Stringable
{
    /**
     * @var list<self>
     */
    private array $parents = [];

    /**
     * @var list<self>
     */
    private array $children = [];

    private int $depth = 0;

    public function __construct(
        public readonly string $name,
        private bool $hidden,
    ) {
    }

    /**
     * @return self[]
     *
     * @phpstan-return list<self>
     */
    public function getParents(): array
    {
        return $this->parents;
    }

    public function addParent(self $parent): void
    {
        if (in_array($this, $parent->getSelfAndAncestors(), true)) {
            throw new RecursionSpecieException($this->name);
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
     * @return self[]
     *
     * @phpstan-return list<self>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(self $child): void
    {
        if (in_array($this, $child->getSelfAndDescendants(), true)) {
            throw new RecursionSpecieException($this->name);
        }

        if (!in_array($child, $this->children, true)) {
            $this->children[] = $child;
        }
    }

    /**
     * @return self[]
     *
     * @phpstan-return list<self>
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
     * @return self[]
     *
     * @phpstan-return list<self>
     */
    public function getSelfAndAncestors(): array
    {
        return [$this, ...$this->getAncestors()];
    }

    /**
     * @return self[]
     *
     * @phpstan-return list<self>
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
     * @return self[]
     *
     * @phpstan-return list<self>
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

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param list<self> $result
     */
    private function addAncestorsRecursionSafely(self $specie, array &$result): void
    {
        foreach ($specie->getParents() as $parent) {
            if (!in_array($parent, $result, true)) {
                $result[] = $parent;
                $this->addAncestorsRecursionSafely($parent, $result);
            }
        }
    }

    /**
     * @param list<self> $result
     */
    private function addDescendantsRecursionSafely(self $specie, array &$result): void
    {
        foreach ($specie->getChildren() as $child) {
            if (!in_array($child, $result, true)) {
                $result[] = $child;
                $this->addDescendantsRecursionSafely($child, $result);
            }
        }
    }

    public function addParentTwoWay(self $parent): void
    {
        $this->addParent($parent);
        $parent->addChild($this);
    }
}
