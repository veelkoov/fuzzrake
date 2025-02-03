<?php

declare(strict_types=1);

namespace App\Species;

use App\Utils\Collections\StringList;
use Veelkoov\Debris\Base\DObjectSet;

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
