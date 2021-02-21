<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;
use TRegx\SafeRegex\Exception\PregException;
use TRegx\SafeRegex\preg;
use UnexpectedValueException;

class StringBuffer
{
    public function __construct(
        private string $buffer,
    ) {
    }

    public function readUntil(string $terminator, bool $trimWhitespaceAfterwards = true): string
    {
        return $this->readUntilRegexp(preg::quote($terminator), $trimWhitespaceAfterwards);
    }

    public function readUntilWhitespace(): string
    {
        return $this->readUntilRegexp("\s");
    }

    public function readToken(): string
    {
        $expectedEmpty = $this->readUntil('|', false);

        if ('' !== $expectedEmpty) {
            throw new UnexpectedValueException("Unexpected content before | delimiter: '$expectedEmpty'");
        }

        return $this->readUntil('|');
    }

    public function skipWhitespace(): void
    {
        $this->buffer = ltrim($this->buffer);
    }

    public function isEmpty(): bool
    {
        return 0 === strlen($this->buffer);
    }

    private function readUntilRegexp(string $terminator, bool $trimWhitespaceAfterwards = true): string
    {
        try {
            $parts = preg::split(pattern($terminator)->delimited(), $this->buffer, 2);
        } catch (PregException $e) {
            throw new RuntimeException("Terminator '$terminator' is not a valid regexp: {$e->getMessage()}");
        }

        if (count($parts) < 2) {
            throw new UnexpectedValueException("Unable to find '{$terminator}' in the remaining buffer '{$this->buffer}'");
        }

        $this->buffer = $parts[1];

        if ($trimWhitespaceAfterwards) {
            $this->skipWhitespace();
        }

        return $parts[0];
    }
}
