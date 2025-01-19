<?php

declare(strict_types=1);

namespace App\Data\Validator;

use App\Data\Definitions\Fields\Field;
use App\Species\SpeciesService;
use App\Utils\PackedStringList;
use Override;

class SpeciesListValidator implements ValidatorInterface
{
    public function __construct(
        private readonly SpeciesService $speciesService,
    ) {
    }

    #[Override]
    public function isValid(Field $field, string $subject): bool
    {
        foreach (PackedStringList::unpack($subject) as $specie) {
            if (!$this->speciesService->validNames->contains($specie)) {
                return false;
            }
        }

        return true;
    }
}
