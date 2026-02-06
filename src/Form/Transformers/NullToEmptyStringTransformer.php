<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?string, ?string>
 */
class NullToEmptyStringTransformer implements DataTransformerInterface
{
    #[Override]
    public function transform($value): mixed
    {
        return $value ?? '';
    }

    #[Override]
    public function reverseTransform($value): mixed
    {
        return $value ?? '';
    }
}
