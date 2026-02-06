<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\Fields\Field;
use App\Data\Validator\Validator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Twig\Attribute\AsTwigFilter;

class AdminHeavyExtensions
{
    public function __construct(
        private readonly Validator $validator,
    ) {
    }

    #[AsTwigFilter('is_valid')]
    public function isValid(Creator $creator, Field $field): bool
    {
        return $this->validator->isValid($creator, $field);
    }
}
