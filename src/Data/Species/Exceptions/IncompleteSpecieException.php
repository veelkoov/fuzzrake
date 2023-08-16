<?php

declare(strict_types=1);

namespace App\Data\Species\Exceptions;

use RuntimeException;

class IncompleteSpecieException extends RuntimeException
{
    public function __construct(string $specieName)
    {
        parent::__construct("Specie '$specieName' was not initialized fully");
    }
}
