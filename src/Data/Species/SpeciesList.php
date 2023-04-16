<?php

declare(strict_types=1);

namespace App\Data\Species;

use App\Data\Species\Exceptions\MissingSpecieException;

class SpeciesList
{
    /**
     * @param Specie[] $items Key = specie name
     *
     * @phpstan-param array<string, Specie> $items Key = specie name
     */
    public function __construct(
        public readonly array $items,
    ) {
    }

    public function getByName(string $name): Specie
    {
        return $this->items[$name] ?? throw new MissingSpecieException($name);
    }

    /**
     * @return string[]
     *
     * @phpstan-return list<string>
     */
    public function getNames(): array
    {
        return array_keys($this->items);
    }

    public function hasName(string $specieName): bool
    {
        return array_key_exists($specieName, $this->items);
    }
}
