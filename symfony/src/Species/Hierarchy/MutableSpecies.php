<?php

declare(strict_types=1);

namespace App\Species\Hierarchy;

use App\Species\SpecieException;
use Override;
use Veelkoov\Debris\Exception\MissingKeyException;
use Veelkoov\Debris\Sets\StringSet;

class MutableSpecies implements Species
{
    private readonly StringMutableSpecieMap $byName;
    private readonly SpecieSet $asTree;

    public function __construct()
    {
        $this->byName = new StringMutableSpecieMap();
        $this->asTree = new SpecieSet();
    }

    #[Override]
    public function getByName(string $name): Specie
    {
        try {
            return $this->byName->get($name);
        } catch (MissingKeyException $exception) {
            throw new SpecieException("No specie named '$name'", previous: $exception);
        }
    }

    #[Override]
    public function getNames(): StringSet
    {
        return $this->byName->getNames()->sorted();
    }

    #[Override]
    public function getVisibleNames(): StringSet
    {
        return $this->byName->filterValues(static fn (Specie $specie): bool => !$specie->hidden)->getNames();
    }

    #[Override]
    public function hasName(string $name): bool
    {
        return $this->byName->hasKey($name);
    }

    #[Override]
    public function getAsTree(): SpecieSet
    {
        return $this->asTree;
    }

    #[Override]
    public function getFlat(): SpecieSet
    {
        return $this->byName->getSpecieSet();
    }

    public function getByNameCreatingMissing(string $name, bool $hidden): MutableSpecie
    {
        return $this->byName->getOrSet($name, static fn () => new MutableSpecie($name, $hidden));
    }

    public function addRootSpecie(MutableSpecie $rootSpecie): void
    {
        $this->asTree->add($rootSpecie);
    }
}
