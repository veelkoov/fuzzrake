<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;
use Throwable;

class UnbelievableRuntimeException extends RuntimeException
{
    public function __construct(Throwable $cause)
    {
        parent::__construct("Impossible happened: {$cause->getMessage()}", $cause->getCode(), $cause);
    }
}
