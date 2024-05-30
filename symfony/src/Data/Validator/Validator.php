<?php

declare(strict_types=1);

namespace App\Data\Validator;

use App\Data\Definitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\PackedStringList;

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
        if (!$field->isValidated()) {
            return true;
        }

        if ($field->isList()) {
            // https://github.com/veelkoov/fuzzrake/issues/221 FIXME: Should not work like that
            $value = PackedStringList::pack($artisan->getStringList($field));
        } else {
            $value = $artisan->getString($field);
        }

        return $this->getValidator($field)->isValid($field, $value);
    }

    private function getValidator(Field $field): ValidatorInterface
    {
        return match ($field) {
            Field::SPECIES_DOES, Field::SPECIES_DOESNT => $this->speciesListValidator,
            default => $this->genericValidator,
        };
    }
}
