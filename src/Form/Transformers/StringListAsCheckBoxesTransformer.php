<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use InvalidArgumentException;
use Override;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<list<string>, string[]>
 */
class StringListAsCheckBoxesTransformer implements DataTransformerInterface
{
    /**
     * @return string[]
     */
    #[Override]
    public function transform(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (null === $value) {
            throw new InvalidArgumentException('Null values are not allowed in model data');
        }

        // @phpstan-ignore-next-line Safety net. Types validation doesn't completely cover forms uses.
        throw new InvalidArgumentException('Unable to transform '.get_debug_type($value));
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function reverseTransform(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value); // Form framework can send non-zero-indexed array
        }

        if (null === $value) {
            return [];
        }

        // @phpstan-ignore-next-line Safety net. Types validation doesn't completely cover forms uses.
        throw new InvalidArgumentException('Unable to reverse transform '.get_debug_type($value));
    }
}
