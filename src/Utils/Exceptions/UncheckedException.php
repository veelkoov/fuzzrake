<?php

declare(strict_types=1);

namespace App\Utils\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Used to wrap checked exceptions which should fall-through without marking all affected methods as throwing.
 *
 * @see UnbelievableRuntimeException
 */
class UncheckedException extends RuntimeException
{
    public function __construct(Throwable $cause, string $message = '')
    {
        parent::__construct('' !== $message ? $message : $cause->getMessage(), $cause->getCode(), $cause);
    }
}
