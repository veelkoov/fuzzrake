<?php

declare(strict_types=1);

namespace App\Data\Fixer;

class DefinedListFixer extends AbstractListFixer
{
    protected static function shouldSort(): bool
    {
        return true;
    }

    protected static function getSeparatorRegexp(): string
    {
        return '[;\n]';
    }
}
