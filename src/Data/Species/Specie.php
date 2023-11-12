<?php

declare(strict_types=1);

namespace App\Data\Species;

use App\Data\Species\Exceptions\IncompleteSpecieException;
use Psl\Iter;
use Stringable;

class Specie implements Stringable
{
    /**
     * @var list<Specie>|null
     */
    private ?array $parents = null;

    /**
     * @var list<Specie>|null
     */
    private ?array $children = null;

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
        return $this->parents ?? throw new IncompleteSpecieException($this->name);
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getChildren(): array
    {
        return $this->children ?? throw new IncompleteSpecieException($this->name);
    }

    /**
     * @return Specie[]
     *
     * @phpstan-return list<Specie>
     */
    public function getAncestors(): array
    {
        $result = $this->parents;

        foreach ($this->getParents() as $parent) {
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

        foreach ($this->getChildren() as $child) {
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
        return [] === $this->getParents();
    }

    public function isLeaf(): bool
    {
        return [] === $this->getChildren();
    }

    public function isVisibleLeaf(): bool
    {
        return Iter\all($this->getChildren(), fn (Specie $child) => $child->hidden);
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
}
