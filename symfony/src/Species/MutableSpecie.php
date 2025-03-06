<?php

declare(strict_types=1);

namespace App\Species;

use Override;
use Psl\Vec;

final class MutableSpecie implements Specie
{
    private SpecieSet $parents;
    private SpecieSet $children;

    public function __construct(
        readonly string $name,
        readonly bool $hidden,
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

    public function getAncestors(): SpecieSet
    {
        return new SpecieSet(Vec\flat_map($this->parents, static fn (Specie $specie) => $specie->getThisAndAncestors()));
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
        return new SpecieSet(Vec\flat_map($this->children,static  fn (Specie $specie) => $specie->getThisAndDescendants()));
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
