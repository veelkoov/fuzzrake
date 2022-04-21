<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class BooleanTransformer implements DataTransformerInterface
{
    use Singleton;

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
