<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use App\Data\Fixer\StringFixerInterface;

final class NoopStringFixer implements StringFixerInterface
{
    public function fix(string $subject): string
    {
        return $subject;
    }
}
