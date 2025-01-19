<?php

declare(strict_types=1);

namespace App\Species;

use Override;
use Psl\Vec;

final class MutableSpecie implements Specie
{
    private SpecieList $parents;
    private SpecieList $children;

    public function __construct(
        readonly string $name,
        readonly bool $hidden,
    ) {
        $this->parents = SpecieList::mut();
        $this->children = SpecieList::mut();
    }

    public function addChild(MutableSpecie $child): void
    {
        if ($this === $child) {
            throw new SpecieException("Cannot add $child->name as a child of itself");
        }

        if ($this->getAncestors()->contains($child)) {
            throw new SpecieException("Recursion when adding child $child->name to $this->name");
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
    public function getParents(): SpecieList
    {
        return $this->parents;
    }

    public function getAncestors(): SpecieList
    {
        return new SpecieList(Vec\flat_map($this->parents, fn (Specie $specie) => $specie->getThisAndAncestors()));
    }

    #[Override]
    public function getThisAndAncestors(): SpecieList
    {
        return $this->getAncestors()->plus($this);
    }

    #[Override]
    public function getChildren(): SpecieList
    {
        return $this->getChildren();
    }

    #[Override]
    public function getDescendants(): SpecieList
    {
        return new SpecieList(Vec\flat_map($this->parents, fn (Specie $specie) => $specie->getThisAndDescendants()));
    }

    #[Override]
    public function getThisAndDescendants(): SpecieList
    {
        return $this->getDescendants()->plus($this);
    }
}
