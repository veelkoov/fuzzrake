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
    /**
     * @var SpeciesListValidator
     */
    private $speciesListValidator;

    /**
     * @var GenericValidator
     */
    private $genericValidator;

    public function __construct(SpeciesListValidator $speciesListValidator)
    {
        $this->speciesListValidator = $speciesListValidator;

        $this->genericValidator = new GenericValidator();
    }

    public function isValid(ArtisanFixWip $artisan, Field $field): bool
    {
        return $this->getValidator($field)->isValid($field, $artisan->getFixed()->get($field));
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
