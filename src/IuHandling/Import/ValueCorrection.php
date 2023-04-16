<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;

class ValueCorrection
{
    public function __construct(
        public readonly Field $field,
        public readonly string $value,
    ) {
    }
}
