<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class StringArrayTransformer implements DataTransformerInterface
{
    use Singleton;

    public function transform($value)
    {
        $value = str_replace("\r\n", "\n", $value);

        return array_filter(explode("\n", $value ?? ''));
    }

    public function reverseTransform($value)
    {
        return implode("\n", array_filter($value));
    }
}
