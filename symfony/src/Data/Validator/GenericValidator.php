<?php

declare(strict_types=1);

namespace App\Data\Validator;

use App\Data\Definitions\Fields\Field;
use Override;

class GenericValidator implements ValidatorInterface
{
    #[Override]
    public function isValid(Field $field, string $subject): bool
    {
        $pattern = $field->validationPattern();

        return null === $pattern || $pattern->test($subject);
    }
}
