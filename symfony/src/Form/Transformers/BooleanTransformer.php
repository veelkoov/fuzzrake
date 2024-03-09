<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<bool, ?string>
 */
class BooleanTransformer implements DataTransformerInterface
{
    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        return match ($value) {
            true    => 'YES',
            false   => 'NO',
            default => null,
        };
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return match ($value) {
            'YES'   => true,
            'NO'    => false,
            default => null,
        };
    }
}
