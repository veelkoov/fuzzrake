<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\Parse;
use App\Utils\StrUtils;
use App\Utils\Traits\Singleton;
use Symfony\Component\Form\DataTransformerInterface;

class BooleanTransformer implements DataTransformerInterface
{
    use Singleton;

    public function transform($value)
    {
        return null === $value ? null : ($value ? 'YES' : 'NO');
    }

    public function reverseTransform($value)
    {
        return Parse::nBool($value ?? '');
    }
}
