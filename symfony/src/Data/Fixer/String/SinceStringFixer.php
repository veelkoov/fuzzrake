<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use App\Data\Fixer\StringFixerInterface;
use Composer\Pcre\Preg;
use Override;

final class SinceStringFixer implements StringFixerInterface
{
    #[Override]
    public function fix(string $subject): string
    {
        return Preg::replace('#(\d{4})-(\d{2})(?:-\d{2})?#', '$1-$2', $subject);
    }
}
