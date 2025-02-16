<?php

declare(strict_types=1);

namespace App\Species;

use Veelkoov\Debris\Base\DStringMap;
use Veelkoov\Debris\StringSet;

/**
 * @extends DStringMap<MutableSpecie>
 */
class StringMutableSpecieMap extends DStringMap
{
    public function getSpecieSet(): SpecieSet
    {
        return new SpecieSet($this->getValuesArray());
    }

    public function getNames(): StringSet
    {
        return $this->getKeys();
    }
}
