<?php

declare(strict_types=1);

namespace App\Species;

use App\Entity\Specie;
use Veelkoov\Debris\Base\DStringMap;

/**
 * @extends DStringMap<Specie>
 */
class StringToSpecieE extends DStringMap
{
    protected static function enforceValueType(mixed $value): Specie
    {
        return $value;
    }
}
