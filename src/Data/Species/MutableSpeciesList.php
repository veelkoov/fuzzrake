<?php

declare(strict_types=1);

namespace App\Data\Species;

use App\Data\Species\Exceptions\MissingSpecieException;

class MutableSpeciesList
{
    /**
     * @var array<string, MutableSpecie> Key = specie name
     */
    private array $items = [];

    public function add(MutableSpecie ...$species): void
    {
        foreach ($species as $specie) {
            $this->items[$specie->name] = $specie;
        }
    }

    public function getByName(string $name): MutableSpecie
    {
        return $this->items[$name] ?? throw new MissingSpecieException($name);
    }

    public function getByNameOrCreate(string $name, bool $hidden): MutableSpecie
    {
        return $this->items[$name] ??= new MutableSpecie($name, $hidden);
    }

    /**
     * @return MutableSpecie[] Key = specie name
     *
     * @phpstan-return array<string, MutableSpecie> Key = specie name
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function toList(): SpeciesList
    {
        $result = new SpeciesList(array_map(
            fn (MutableSpecie $specie) => new Specie($specie->name, $specie->isHidden(), $specie->getDepth()),
            $this->items,
        ));

        foreach ($result->items as $specie) {
            $specie->setParents(array_map(
                fn (MutableSpecie $parent) => $result->getByName($parent->name),
                $this->items[$specie->name]->getParents(),
            ));

            $specie->setChildren(array_map(
                fn (MutableSpecie $parent) => $result->getByName($parent->name),
                $this->items[$specie->name]->getChildren(),
            ));
        }

        return $result;
    }
}
