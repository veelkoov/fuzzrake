<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use App\Utils\PackedStringList;

class UrlListFixer implements FixerInterface
{
    public function __construct(
        private readonly UrlFixer $urlFixer,
    ) {
    }

    public function fix(string $subject): string
    {
        return PackedStringList::pack(array_map($this->urlFixer->fix(...), PackedStringList::unpack($subject)));
    }
}
