<?php

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\StrListFixerInterface;
use Override;

final class NoopStrListFixer implements StrListFixerInterface
{
    #[Override]
    public function fix(array $subject): array
    {
        return $subject;
    }
}
