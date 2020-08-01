<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\DataTransformerInterface;

class StringArrayTransformer implements DataTransformerInterface
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
        $value = str_replace("\r\n", "\n", $value);

        return array_filter(explode("\n", $value ?? ''));
    }

    public function reverseTransform($value)
    {
        return implode("\n", array_filter($value));
    }
}
