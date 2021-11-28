<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class StringArrayTransformer implements DataTransformerInterface
{
    use Singleton;

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        $value = str_replace("\r\n", "\n", $value);

        return array_filter(explode("\n", $value ?? ''));
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return implode("\n", array_filter($value));
    }
}
