<?php

declare(strict_types=1);

namespace App\Species;

use App\Entity\Specie;
use Veelkoov\Debris\Base\DScalarMap;
use Veelkoov\Debris\StringList;

/**
 * @extends DScalarMap<string, Specie>
 */
class StringSpecieMap extends DScalarMap
{
    public function getNames(): StringList
    {
        return new StringList($this->getKeysArray());
    }
}
