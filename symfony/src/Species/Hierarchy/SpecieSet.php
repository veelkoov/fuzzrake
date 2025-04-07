<?php

declare(strict_types=1);

namespace App\Species\Hierarchy;

use App\Utils\Collections\StringList;
use Veelkoov\Debris\Base\DSet;

/**
 * @extends DSet<Specie>
 */
class SpecieSet extends DSet
{
    public function getNames(): StringList
    {
        return StringList::mapFrom($this, static fn (Specie $specie) => $specie->getName());
    }
}
