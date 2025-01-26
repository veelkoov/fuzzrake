<?php

declare(strict_types=1);

namespace App\Species;

use Veelkoov\Debris\DScalarMap;
use Veelkoov\Debris\StringList;

/**
 * @extends DScalarMap<string, MutableSpecie>
 */
class StringMutableSpecieMap extends DScalarMap
{
    public function getValues(): SpecieSet
    {
        return new SpecieSet($this->items);
    }

    public function getKeys(): StringList
    {
        return new StringList(array_keys($this->items));
    }
}
