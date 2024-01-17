<?php

declare(strict_types=1);

namespace App\Data\Validator;

use App\Data\Definitions\Fields\Field;
use App\Service\SpeciesService;
use App\Utils\StringList;

class SpeciesListValidator implements ValidatorInterface
{
    public function __construct(
        private readonly SpeciesService $speciesService,
    ) {
    }

    public function isValid(Field $field, string $subject): bool
    {
        foreach (StringList::unpack($subject) as $specie) {
            if (!in_array($specie, $this->speciesService->getValidNames())) {
                return false;
            }
        }

        return true;
    }
}
