<?php

declare(strict_types=1);

namespace App\Data\Fixer;

class StringFixer extends AbstractStringFixer
{
    /**
     * @param psFixerConfig $strings
     */
    public function __construct(array $strings)
    {
        parent::__construct($strings);
    }
}
