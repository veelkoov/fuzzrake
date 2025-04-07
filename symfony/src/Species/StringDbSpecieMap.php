<?php

declare(strict_types=1);

namespace App\Species;

use App\Entity\Specie as SpecieE;
use Veelkoov\Debris\Base\DStringMap;
use Veelkoov\Debris\StringSet;

/**
 * @extends DStringMap<SpecieE>
 */
class StringDbSpecieMap extends DStringMap
{
    public function getNames(): StringSet
    {
        return $this->getKeys();
    }
}
