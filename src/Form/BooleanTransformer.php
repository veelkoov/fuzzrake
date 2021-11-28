<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Parse;
use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class BooleanTransformer implements DataTransformerInterface
{
    use Singleton;

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        return null === $value ? null : ($value ? 'YES' : 'NO');
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return Parse::nBool($value ?? '');
    }
}
