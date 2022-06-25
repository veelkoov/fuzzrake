<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class NoopFixer implements FixerInterface
{
    public function fix(string $subject): string
    {
        return $subject;
    }
}
