<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;

readonly class ValueCorrection
{
    /**
     * @param list<string>|string|bool $value
     */
    public function __construct(
        public Field $field,
        public array|string|bool $value,
    ) {
    }
}
