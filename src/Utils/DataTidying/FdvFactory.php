<?php

declare(strict_types=1);

namespace App\Utils\DataTidying;

use App\Data\Fixer\Fixer;
use App\Data\Validator\Validator;

class FdvFactory
{
    public function __construct(
        private readonly Fixer $fixer,
        private readonly Validator $validator,
    ) {
    }

    public function create(Printer $printer): FixerDifferValidator
    {
        return new FixerDifferValidator($this->fixer, $this->validator, $printer);
    }
}
