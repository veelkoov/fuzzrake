<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class IntroFixer extends StringFixer
{
    public function fix(string $fieldName, string $subject): string
    {
        return parent::fix($fieldName, str_replace("\n", ' ', $subject));
    }
}
