<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use Override;
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

    #[Override]
    protected static function isValidValue(mixed $value): bool
    {
        return $value instanceof Field;
    }

    #[Override]
    protected static function enforceValueType(mixed $value): Field
    {
        return $value;
    }
}
