<?php

declare(strict_types=1);

namespace App\Utils;

use App\DataDefinitions\Ages;
use App\DataDefinitions\Fields\Field;

interface FieldReadInterface
{
    /**
     * @return Ages|string[]|string|int|bool|null
     */
    public function get(Field $field): mixed;

    public function getString(Field $field): string;
}
