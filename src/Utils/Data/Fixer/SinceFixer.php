<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;

class SinceFixer implements FixerInterface
{
    public function fix(string $fieldName, string $subject): string
    {
        return Regexp::replace('#(\d{4})-(\d{2})(?:-\d{2})?#', '$1-$2', trim($subject));
    }
}
