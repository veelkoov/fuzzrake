<?php

declare(strict_types=1);

namespace App\Utils\DataTidying;

use App\Utils\Data\Fixer;
use App\Utils\Data\Validator;

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
