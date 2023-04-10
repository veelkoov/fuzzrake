<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\StringList;

class UrlListFixer implements FixerInterface
{
    public function __construct(
        private readonly UrlFixer $urlFixer,
    ) {
    }

    public function fix(string $subject): string
    {
        return StringList::pack(array_map($this->urlFixer->fix(...), StringList::unpack($subject)));
    }
}
