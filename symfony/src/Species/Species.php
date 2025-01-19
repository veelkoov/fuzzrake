<?php

namespace App\Species;

use Veelkoov\Debris\StringList;

interface Species
{
    public function getByName(string $name): Specie;

    public function getNames(): StringList;
    public function getVisibleNames(): StringList;

    public function hasName(string $name): bool;

    /**
     * @return list<Specie>
     */
    public function getAsTree(): array;

    /**
     * @return list<Specie>
     */
    public function getFlat(): array;
}
