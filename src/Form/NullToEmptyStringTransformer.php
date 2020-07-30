<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\DataTransformerInterface;

class NullToEmptyStringTransformer implements DataTransformerInterface
{
    private static ?self $INSTANCE = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        return self::$INSTANCE ?? self::$INSTANCE = new self();
    }

    public function transform($value)
    {
        return $value ?? '';
    }

    public function reverseTransform($value)
    {
        return $value ?? '';
    }
}
