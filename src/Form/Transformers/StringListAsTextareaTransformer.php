<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use App\Utils\Enforce;
use App\Utils\PackedStringList;
use Override;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<list<string>, string>
 */
class StringListAsTextareaTransformer implements DataTransformerInterface
{
    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    #[Override]
    public function transform($value): mixed
    {
        return PackedStringList::pack($value ?? []);
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    #[Override]
    public function reverseTransform($value): mixed
    {
        return PackedStringList::unpack(Enforce::nString($value) ?? '');
    }
}
