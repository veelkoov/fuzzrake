<?php

declare(strict_types=1);

namespace App\Species\Hierarchy;

use Veelkoov\Debris\Base\DSet;
use Veelkoov\Debris\StringSet;

/**
 * @extends DSet<Specie>
 */
class SpecieSet extends DSet
{
    public function getNames(): StringSet
    {
        return StringSet::mapFrom($this, static fn (Specie $specie) => $specie->getName());
    }
}
