<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\Service\Species;
use App\Utils\Artisan\Field;
use App\Utils\StringList;

class SpeciesListValidator implements ValidatorInterface
{
    private Species $species;

    public function __construct(Species $species)
    {
        $this->species = $species;
    }

    public function isValid(Field $field, $subject): bool
    {
        foreach (StringList::unpack($subject) as $specie) {
            if (!in_array($specie, $this->species->getValidChoices())) {
                return false;
            }
        }

        return true;
    }
}
