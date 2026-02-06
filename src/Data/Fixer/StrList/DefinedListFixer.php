<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use Override;

final class DefinedListFixer extends AbstractListFixer
{
    #[Override]
    protected function shouldSort(): bool
    {
        return true;
    }

    #[Override]
    protected function getSeparatorRegexp(): ?string
    {
        return null;
    }

    #[Override]
    protected function fixItem(string $subject): string
    {
        return $subject;
    }
}
