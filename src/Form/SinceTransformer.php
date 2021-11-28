<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class SinceTransformer implements DataTransformerInterface
{
    use Singleton;

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        return pattern('^\d{4}-\d{2}$')->test($value) ? $value.'-01' : '';
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return substr($value, 0, 7);
    }
}
