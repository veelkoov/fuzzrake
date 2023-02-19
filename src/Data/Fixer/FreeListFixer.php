<?php

declare(strict_types=1);

namespace App\Data\Fixer;

class FreeListFixer extends AbstractListFixer
{
    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return "\n";
    }
}
