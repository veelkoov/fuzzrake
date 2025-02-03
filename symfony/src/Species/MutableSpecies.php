<?php

declare(strict_types=1);

namespace App\Species;

use App\Utils\Collections\StringList;

class MutableSpecies implements Species
{
    private StringMutableSpecieMap $byName;
    private SpecieSet $asTree;

    public function __construct()
    {
        $this->byName = StringMutableSpecieMap::mut();
        $this->asTree = SpecieSet::mut();
    }

    public function getByName(string $name): Specie
    {
        return $this->byName->getOrDefault($name, fn () => throw new SpecieException("No specie named '$name'"));
    }

    public function getNames(): StringList
    {
        return $this->byName->getKeys()->sorted();
    }

    public function getVisibleNames(): StringList
    {
        return $this->byName->filterValues(fn (MutableSpecie $specie): bool => !$specie->hidden)->getKeys();
    }

    public function hasName(string $name): bool
    {
        return $this->byName->hasKey($name);
    }

    public function getAsTree(): SpecieSet
    {
        return $this->asTree;
    }

    public function getFlat(): SpecieSet
    {
        return $this->byName->getValues();
    }

    public function getByNameCreatingMissing(string $name, bool $hidden): MutableSpecie
    {
        return $this->byName->getOrSet($name, fn () => new MutableSpecie($name, $hidden));
    }

    public function addRootSpecie(MutableSpecie $rootSpecie): void
    {
        $this->asTree->add($rootSpecie);
    }
}
