<?php

declare(strict_types=1);

namespace App\Species\Hierarchy;

use App\Species\SpecieException;
use Override;

final class MutableSpecie implements Specie
{
    private readonly SpecieSet $parents;
    private readonly SpecieSet $children;

    public function __construct(
        public readonly string $name,
        public readonly bool $hidden,
    ) {
        $this->parents = new SpecieSet();
        $this->children = new SpecieSet();
    }

    public function addChild(MutableSpecie $child): void
    {
        if ($this === $child) {
            throw new SpecieException("Cannot add '$child->name' as a child of itself");
        }

        if ($this->getAncestors()->contains($child)) {
            throw new SpecieException("Recursion when adding child '$child->name' to '$this->name'");
        }

        if (!$this->children->contains($child)) {
            $this->children->add($child);
        }

        if (!$child->parents->contains($this)) {
            $child->parents->add($this);
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
    public function getParents(): SpecieSet
    {
        return $this->parents;
    }

    #[Override]
    public function getAncestors(): SpecieSet
    {
        $result = new SpecieSet();

        foreach ($this->parents as $parent) {
            $result->addAll($parent->getThisAndAncestors());
        }

        return $result;
    }

    #[Override]
    public function getThisAndAncestors(): SpecieSet
    {
        return $this->getAncestors()->plus($this);
    }

    #[Override]
    public function getChildren(): SpecieSet
    {
        return $this->children;
    }

    #[Override]
    public function getDescendants(): SpecieSet
    {
        $result = new SpecieSet();

        foreach ($this->children as $child) {
            $result->addAll($child->getThisAndDescendants());
        }

        return $result;
    }

    #[Override]
    public function getThisAndDescendants(): SpecieSet
    {
        return $this->getDescendants()->plus($this);
    }

    #[Override]
    public function getDepth(): int
    {
        if ($this->parents->isEmpty()) {
            return 0;
        } else {
            return $this->parents->max(static fn (Specie $specie) => $specie->getDepth()) + 1;
        }
    }
}
