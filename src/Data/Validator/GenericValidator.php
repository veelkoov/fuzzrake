<?php

declare(strict_types=1);

namespace App\Data\Validator;

use App\Data\Definitions\Fields\Field;
use Composer\Pcre\Preg;
use Override;

class GenericValidator implements ValidatorInterface
{
    #[Override]
    public function isValid(Field $field, string $subject): bool
    {
        $pattern = $field->validationPattern();

        return null === $pattern || Preg::isMatch($pattern, $subject);
    }
}
