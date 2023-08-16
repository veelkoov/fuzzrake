<?php

declare(strict_types=1);

namespace App\Data\Species\Exceptions;

use RuntimeException;

class RecursionSpecieException extends RuntimeException
{
    public function __construct(string $specieName)
    {
        parent::__construct("Recursion in specie: $specieName");
    }
}
