<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\UrlFixerGeneric;
use Override;

final class UrlListStringFixer extends AbstractListFixer
{
    public function __construct(
        private readonly UrlFixerGeneric $urlFixer,
    ) {
    }

    #[Override]
    protected function fixItem(string $subject): string
    {
        return $this->urlFixer->fix($subject);
    }
}
