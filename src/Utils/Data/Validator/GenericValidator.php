<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\Utils\Artisan\Field;
use App\Utils\Regexp\Utils as Regexp;

class GenericValidator implements ValidatorInterface
{
    public function validate(Field $field, $subject): bool
    {
        return null === $field->validationRegexp() || Regexp::match($field->validationRegexp(), $subject);
    }
}
