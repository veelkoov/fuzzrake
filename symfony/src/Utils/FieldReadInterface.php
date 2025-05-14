<?php

declare(strict_types=1);

namespace App\Utils;

use App\Data\Definitions\Fields\Field;

interface FieldReadInterface
{
    /**
     * @return psPhpFieldValue
     */
    public function get(Field $field): mixed;

    public function getString(Field $field): string;

    /**
     * @return list<string>
     */
    public function getStringList(Field $field): array;

    public function hasData(Field $field): bool;
}
