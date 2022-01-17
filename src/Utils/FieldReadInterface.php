<?php

declare(strict_types=1);

namespace App\Utils;

use App\DataDefinitions\Fields\Field;

interface FieldReadInterface
{
    /**
     * @throws DataInputException
     */
    public function get(Field $field);
}
