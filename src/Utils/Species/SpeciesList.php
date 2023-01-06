<?php

declare(strict_types=1);

namespace App\Utils\Species;

class SpeciesList
{
    /**
     * @var array<string, Specie> Key = specie name
     */
    private array $items = [];

    public function add(Specie ...$species): void
    {
        foreach ($species as $specie) {
            $this->items[$specie->getName()] = $specie;
        }
    }

    public function getByName(string $name): Specie
    {
        return $this->items[$name] ?? throw new MissingSpecieException("Specie '$name' does not exist");
    }

    public function getByNameOrCreate(string $name, bool $hidden): Specie
    {
        return $this->items[$name] ??= new Specie($name, $hidden);
    }

    /**
     * @return list<string>
     */
    public function getNames(): array
    {
        return array_keys($this->items);
    }

    public function hasName(string $specieName): bool
    {
        return array_key_exists($specieName, $this->items);
    }

    /**
     * @return list<Specie>
     */
    public function getAll(): array
    {
        return array_values($this->items);
    }
}
