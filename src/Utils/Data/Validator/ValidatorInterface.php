<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\DataDefinitions\Fields\Field;

interface ValidatorInterface
{
    public function isValid(Field $field, string $subject): bool;
}
