<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use UnexpectedValueException;

class StringBuffer
{
    private string $buffer;

    public function __construct(string $initialValue)
    {
        $this->buffer = $initialValue;
    }

    public function readUntil(string $terminator): string
    {
        $parts = explode($terminator, $this->buffer, 2);

        if (count($parts) < 2) {
            throw new UnexpectedValueException("Unable to find '{$terminator}' in the remaining buffer '{$this->buffer}'");
        }

        $this->buffer = $parts[1];

        return $parts[0];
    }

    public function skipWhitespace(): void
    {
        $this->buffer = ltrim($this->buffer);
    }

    public function isEmpty(): bool
    {
        return 0 === strlen($this->buffer);
    }

    public function flush(): void
    {
        $this->buffer = '';
    }
}
