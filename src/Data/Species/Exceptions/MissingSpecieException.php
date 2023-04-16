<?php

declare(strict_types=1);

namespace App\Data\Species\Exceptions;

use RuntimeException;

class MissingSpecieException extends RuntimeException
{
    public function __construct(string $specieName)
    {
        parent::__construct("Specie '$specieName' does not exist");
    }
}
