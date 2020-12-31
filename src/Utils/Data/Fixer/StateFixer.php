<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class StateFixer extends AbstractStringFixer
{
    private StringFixer $stringFixer;

    public function __construct(array $states, StringFixer $stringFixer)
    {
        parent::__construct($states);

        $this->stringFixer = $stringFixer;
    }

    public function fix(string $fieldName, string $subject): string
    {
        return parent::fix($fieldName, $this->stringFixer->fix($fieldName, $subject));
    }
}
