<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use Veelkoov\Debris\Base\DStringMap;
use Veelkoov\Debris\StringSet;

/**
 * @extends DStringMap<Field>
 */
final class FieldsList extends DStringMap
{
    public function names(): StringSet
    {
        return $this->getKeys();
    }
}
