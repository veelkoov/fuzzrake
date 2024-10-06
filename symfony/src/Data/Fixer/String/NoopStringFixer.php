<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use App\Data\Fixer\StringFixerInterface;
use Override;

final class NoopStringFixer implements StringFixerInterface
{
    #[Override]
    public function fix(string $subject): string
    {
        return $subject;
    }
}
