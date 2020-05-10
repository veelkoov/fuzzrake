<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class StateFixer extends AbstractStringFixer
{
    public function __construct(array $states)
    {
        parent::__construct($states);
    }
}
