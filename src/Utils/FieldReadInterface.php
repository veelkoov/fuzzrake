<?php

declare(strict_types=1);

namespace App\Utils;

use App\DataDefinitions\Fields\Field;

interface FieldReadInterface
{
    public function get(Field $field): mixed;
}
