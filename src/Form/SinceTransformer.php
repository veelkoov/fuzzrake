<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class SinceTransformer implements DataTransformerInterface
{
    use Singleton;

    /** @noinspection PhpMissingReturnTypeInspection Overridden */
    public function transform($value)
    {
        return pattern('^\d{4}-\d{2}$')->test($value) ? $value.'-01' : '';
    }

    public function reverseTransform($value)
    {
        return substr($value, 0, 7);
    }
}
