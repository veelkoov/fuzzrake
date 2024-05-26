<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

final class DefinedListFixer extends AbstractListFixer
{
    protected function shouldSort(): bool
    {
        return true;
    }

    protected function getSeparatorRegexp(): ?string
    {
        return null;
    }

    protected function fixItem(string $subject): string
    {
        return $subject;
    }
}
