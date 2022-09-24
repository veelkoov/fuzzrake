<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Validator\GenericValidator;
use App\Utils\Data\Validator\SpeciesListValidator;
use App\Utils\Data\Validator\ValidatorInterface;

class Validator
{
    private readonly GenericValidator $genericValidator;

    public function __construct(
        private readonly SpeciesListValidator $speciesListValidator,
    ) {
        $this->genericValidator = new GenericValidator();
    }

    public function isValid(Artisan $artisan, Field $field): bool
    {
        return !$field->isValidated() || $this->getValidator($field)->isValid($field, $artisan->getString($field));
    }

    private function getValidator(Field $field): ValidatorInterface
    {
        return match ($field) {
            Field::SPECIES_DOES, Field::SPECIES_DOESNT => $this->speciesListValidator,
            default => $this->genericValidator,
        };
    }
}
