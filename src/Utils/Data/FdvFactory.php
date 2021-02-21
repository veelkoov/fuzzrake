<?php

declare(strict_types=1);

namespace App\Utils\Data;

class FdvFactory
{
    public function __construct(
        private Fixer $fixer,
        private Validator $validator,
    ) {
    }

    public function create(Printer $printer): FixerDifferValidator
    {
        return new FixerDifferValidator($this->fixer, $this->validator, $printer);
    }
}
