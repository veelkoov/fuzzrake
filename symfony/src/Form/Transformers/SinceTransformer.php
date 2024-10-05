<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<string, string>
 */
class SinceTransformer implements DataTransformerInterface
{
    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    #[Override]
    public function transform($value): mixed
    {
        return pattern('^\d{4}-\d{2}$')->test($value ?? '') ? $value.'-01' : '';
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    #[Override]
    public function reverseTransform($value): mixed
    {
        return substr($value ?? '', 0, 7);
    }
}
