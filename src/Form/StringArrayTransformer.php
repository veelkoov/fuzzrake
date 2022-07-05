<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\StringList;
use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<string, string[]>
 */
class StringArrayTransformer implements DataTransformerInterface
{
    use Singleton;

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        return array_filter(StringList::unpack($value));
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return StringList::pack(array_filter($value ?? []));
    }
}
