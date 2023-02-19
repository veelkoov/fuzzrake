<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\Data\Definitions\Fields\Field;

class GenericValidator implements ValidatorInterface
{
    public function isValid(Field $field, string $subject): bool
    {
        $pattern = $field->validationPattern();

        return null === $pattern || $pattern->test($subject);
    }
}
