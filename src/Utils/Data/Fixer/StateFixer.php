<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class StateFixer extends AbstractStringFixer
{
    public function __construct(
        array $states,
        private StringFixer $stringFixer,
    ) {
        parent::__construct($states);
    }

    public function fix(string $fieldName, string $subject): string
    {
        return parent::fix($fieldName, $this->stringFixer->fix($fieldName, $subject));
    }
}
