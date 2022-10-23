<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class SinceFixer implements FixerInterface
{
    public function fix(string $subject): string
    {
        return pattern('(\d{4})-(\d{2})(?:-\d{2})?')
            ->replace($subject)
            ->withReferences('$1-$2');
    }
}
