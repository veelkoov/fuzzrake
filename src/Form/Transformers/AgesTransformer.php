<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use App\DataDefinitions\Ages;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<Ages, ?string>
 */
class AgesTransformer implements DataTransformerInterface
{
    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        return $value?->value;
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return Ages::tryFrom($value ?? '');
    }
}
