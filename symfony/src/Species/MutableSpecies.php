<?php

declare(strict_types=1);

namespace App\Species;

class MutableSpecies implements Species
{
    /**
     * @var array<string, MutableSpecie>
     */
    private array $byName = [];

    /**
     * @var list<MutableSpecie>
     */
    private array $asTree = [];

    public function __construct(
    ) {
    }

    public function getByName(string $name): Specie
    {
        return $this->byName[$name] ?? throw new SpecieException("No specie named '$name'");
    }

    public function getNames(): array
    {
        return array_keys($this->byName); // TODO: Sort?
    }

    public function getVisibleNames(): array
    {
        return array_keys(array_filter($this->byName, fn (MutableSpecie $specie): bool => !$specie->hidden));
    }

    public function hasName(string $name): bool
    {
        return array_key_exists($name, $this->byName);
    }

    public function getAsTree(): array
    {
        return $this->asTree;
    }

    public function getFlat(): array
    {
        return array_values($this->byName);
    }

    public function getByNameCreatingMissing(string $name, bool $hidden): MutableSpecie
    {
        return $this->byName[$name] ??= new MutableSpecie($name, $hidden);
    }

    public function addRootSpecie(MutableSpecie $rootSpecie): void
    {
        $this->asTree[] = $rootSpecie;
    }
}
