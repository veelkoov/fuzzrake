<?php

declare(strict_types=1);

namespace App\Species\Hierarchy;

use Veelkoov\Debris\StringSet;

interface Species
{
    public function getByName(string $name): Specie;

    public function getNames(): StringSet;

    public function getVisibleNames(): StringSet;

    public function hasName(string $name): bool;

    public function getAsTree(): SpecieSet;

    public function getFlat(): SpecieSet;
}
