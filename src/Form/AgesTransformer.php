<?php

declare(strict_types=1);

namespace App\Form;

use App\DataDefinitions\Ages;
use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class AgesTransformer implements DataTransformerInterface
{
    use Singleton;

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        return $value?->value ?? '';
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return Ages::tryFrom($value ?? '');
    }
}
