<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;

readonly class ValueCorrection
{
    public function __construct(
        public Field $field,
        public string|bool $value,
    ) {
    }
}
