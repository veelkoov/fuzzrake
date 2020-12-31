<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\Utils\Artisan\Field;
use App\Utils\Species\Species;
use App\Utils\StringList;

class SpeciesListValidator implements ValidatorInterface
{
    private Species $speciesService;

    public function __construct(Species $speciesService)
    {
        $this->speciesService = $speciesService;
    }

    public function isValid(Field $field, $subject): bool
    {
        foreach (StringList::unpack($subject) as $specie) {
            if (!in_array($specie, $this->speciesService->getValidChoicesList())) {
                return false;
            }
        }

        return true;
    }
}
