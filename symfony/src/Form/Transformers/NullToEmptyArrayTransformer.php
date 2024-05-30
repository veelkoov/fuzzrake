<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<list<string>, list<string>>
 */
class NullToEmptyArrayTransformer implements DataTransformerInterface
{
    public function transform($value): mixed
    {
        return $value ?? [];
    }

    public function reverseTransform($value): mixed
    {
        return $value ?? [];
    }
}
