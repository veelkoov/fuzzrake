<?php

declare(strict_types=1);

namespace App\Utils\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Used to wrap checked exceptions which are impossible to happen in a particular case, but are part of some interface.
 *
 * @see UncheckedException
 */
class UnbelievableRuntimeException extends RuntimeException
{
    public function __construct(Throwable $cause)
    {
        parent::__construct("Impossible happened: {$cause->getMessage()}", $cause->getCode(), $cause);
    }
}
