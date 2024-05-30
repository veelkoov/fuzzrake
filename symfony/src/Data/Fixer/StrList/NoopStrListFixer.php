<?php

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\StrListFixerInterface;

final class NoopStrListFixer implements StrListFixerInterface
{
    public function fix(array $subject): array
    {
        return $subject;
    }
}
