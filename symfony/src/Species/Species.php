<?php

namespace App\Species;

use Veelkoov\Debris\StringList;

interface Species
{
    public function getByName(string $name): Specie;

    public function getNames(): StringList;

    public function getVisibleNames(): StringList;

    public function hasName(string $name): bool;

    public function getAsTree(): SpecieSet;

    public function getFlat(): SpecieSet;
}
