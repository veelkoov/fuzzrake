<?php

namespace App\Species;

use App\Utils\Collections\StringList;
use Veelkoov\Debris\StringSet;

interface Species
{
    public function getByName(string $name): Specie;

    public function getNames(): StringSet;

    public function getVisibleNames(): StringList;

    public function hasName(string $name): bool;

    public function getAsTree(): SpecieSet;

    public function getFlat(): SpecieSet;
}
