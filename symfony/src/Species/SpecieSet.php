<?php

declare(strict_types=1);

namespace App\Species;

use Veelkoov\Debris\Base\DObjectSet;
use Veelkoov\Debris\StringList;

/**
 * @extends DObjectSet<Specie>
 */
class SpecieSet extends DObjectSet
{
    public function getNames(): StringList
    {
        return StringList::mapFrom($this->items, fn (Specie $specie) => $specie->getName());
    }
}
