<?php

declare(strict_types=1);

namespace App\Data\Species\Exceptions;

use RuntimeException;

class SharedRootSpecieException extends RuntimeException
{
    /**
     * @param string[] $sharedSpecieNames
     */
    public function __construct(array $sharedSpecieNames)
    {
        parent::__construct('Species configuration error: species shared between root species: '.implode(', ', $sharedSpecieNames));
    }
}
