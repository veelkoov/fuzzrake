<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\DataTransformerInterface;

class StringArrayTransformer implements DataTransformerInterface
{
    private static $INSTANCE = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        return self::$INSTANCE ?? self::$INSTANCE = new self();
    }

    public function transform($value)
    {
        return explode("\n", $value);
    }

    public function reverseTransform($value)
    {
        return implode("\n", array_filter($value));
    }
}
