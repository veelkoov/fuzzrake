<?php

declare(strict_types=1);

namespace App\Species;

use Override;
use Psl\Vec;

final class MutableSpecie implements Specie
{
    /**
     * @var list<MutableSpecie>
     */
    private array $parents = [];

    /**
     * @var list<MutableSpecie>
     */
    private array $children = [];

    public function __construct(
        readonly string $name,
        readonly bool $hidden,
    ) {
    }

    public function addChild(MutableSpecie $child): void
    {
        if ($this === $child) {
            throw new SpecieException("Cannot add $child->name as a child of itself");
        }

        if (in_array($child, $this->getAncestors(), true)) {
            throw new SpecieException("Recursion when adding child $child->name to $this->name");
        }

        if (!in_array($child, $this->children, true)) {
            $this->children[] = $child;
        }

        if (!in_array($this, $child->parents, true)) {
            $child->parents[] = $this;
        }
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getHidden(): bool
    {
        return $this->hidden;
    }

    #[Override]
    public function getParents(): array
    {
        return $this->parents;
    }

    public function getAncestors(): array
    {
        return [...$this->parents, ...Vec\flat_map($this->parents, fn (self $specie): array => $specie->getAncestors())];
    }

    #[Override]
    public function getThisAndAncestors(): array
    {
        return [$this, ...$this->getAncestors()];
    }

    #[Override]
    public function getChildren(): array
    {
        return $this->getChildren();
    }

    #[Override]
    public function getDescendants(): array
    {
        return [...$this->children, ...Vec\flat_map($this->parents, fn (self $specie): array => $specie->getDescendants())];
    }

    #[Override]
    public function getThisAndDescendants(): array
    {
        return [$this, ...$this->getDescendants()];
    }
}
