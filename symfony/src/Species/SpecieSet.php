<?php

declare(strict_types=1);

namespace App\Species;

use App\Utils\Collections\StringList;
use Veelkoov\Debris\Base\DSet;

/**
 * @extends DSet<Specie>
 */
class SpecieSet extends DSet
{
    public function getNames(): StringList
    {
        return StringList::mapFrom($this->getValuesArray(), static fn (Specie $specie) => $specie->getName());
    }
}
