<?php

declare(strict_types=1);

namespace App\Utils;

use App\Data\Definitions\Fields\Field;

interface FieldReadInterface
{
    /**
     * @return psFieldValue
     */
    public function get(Field $field): mixed;

    public function getString(Field $field): string;

    public function hasData(Field $field): bool;
}
