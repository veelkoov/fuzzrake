<?php

declare(strict_types=1);

namespace App\Data\Fixer;

class StateFixer extends AbstractStringFixer
{
    /**
     * @param psFixerConfig $states
     */
    public function __construct(
        array $states,
        private readonly StringFixer $stringFixer,
    ) {
        parent::__construct($states);
    }

    public function fix(string $subject): string
    {
        return parent::fix($this->stringFixer->fix($subject));
    }
}
