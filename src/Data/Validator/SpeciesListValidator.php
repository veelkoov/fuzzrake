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
        return array_all(PackedStringList::unpack($subject),
            fn ($specie) => $this->speciesService->getValidNames()->contains($specie));
    }
}
