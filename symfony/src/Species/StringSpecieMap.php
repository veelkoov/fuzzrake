<?php

declare(strict_types=1);

namespace App\Species;

use App\Entity\Specie;
use Veelkoov\Debris\Base\DStringMap;
use Veelkoov\Debris\StringSet;

/**
 * @extends DStringMap<Specie>
 */
class StringSpecieMap extends DStringMap
{
    public function getNames(): StringSet
    {
        return $this->getKeys();
    }
}
