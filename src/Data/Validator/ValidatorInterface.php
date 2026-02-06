<?php

declare(strict_types=1);

namespace App\Data\Validator;

use App\Data\Definitions\Fields\Field;

interface ValidatorInterface
{
    public function isValid(Field $field, string $subject): bool;
}
