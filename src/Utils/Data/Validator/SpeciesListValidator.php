<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\Utils\Artisan\Field;

class SpeciesListValidator implements ValidatorInterface
{
    public function __construct(array $species)
    {
    }

    public function validate(Field $field, string $subject): bool
    {
        return true; // FIXME
    }
}
