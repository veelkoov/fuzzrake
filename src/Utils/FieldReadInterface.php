<?php

declare(strict_types=1);

namespace App\Utils;

use App\DataDefinitions\Fields\Field;

interface FieldReadInterface
{
    /**
     * @return psFieldValue
     */
    public function get(Field $field): mixed;

    public function getString(Field $field): string;
}
