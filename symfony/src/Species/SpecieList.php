<?php

declare(strict_types=1);

namespace App\Species;

use Veelkoov\Debris\DList;
use Veelkoov\Debris\StringList;

/**
 * @extends DList<Specie>
 */
class SpecieList extends DList
{
    public function getNames(): StringList
    {
        return new StringList(array_map(fn (Specie $specie) => $specie->getName(), $this->items));
    }
}
