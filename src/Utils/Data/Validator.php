<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\Data\Validator\GenericValidator;
use App\Utils\Data\Validator\SpeciesListValidator;
use App\Utils\Data\Validator\ValidatorInterface;

class Validator
{
    private GenericValidator $genericValidator;

    public function __construct(
        private SpeciesListValidator $speciesListValidator,
    ) {
        $this->genericValidator = new GenericValidator();
    }

    public function isValid(ArtisanChanges $artisan, Field $field): bool
    {
        return $this->getValidator($field)->isValid($field, $artisan->getChanged()->get($field));
    }

    private function getValidator(Field $field): ValidatorInterface
    {
        return match ($field->name()) {
            Fields::SPECIES_DOES, Fields::SPECIES_DOESNT => $this->speciesListValidator,
            default => $this->genericValidator,
        };
    }
}
