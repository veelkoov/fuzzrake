<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class NullToEmptyStringTransformer implements DataTransformerInterface
{
    use Singleton;

    public function transform($value)
    {
        return $value ?? '';
    }

    public function reverseTransform($value)
    {
        return $value ?? '';
    }
}
