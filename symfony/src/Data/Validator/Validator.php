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
            $value = PackedStringList::pack($artisan->getStringList($field)); // FIXME: Should not work like that
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
