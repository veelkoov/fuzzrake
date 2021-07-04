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
        switch ($field->name()) {
            case Fields::SPECIES_DOES:
            case Fields::SPECIES_DOESNT:
                return $this->speciesListValidator;

            default:
                return $this->genericValidator;
        }
    }
}
