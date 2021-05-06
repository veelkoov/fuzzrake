<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;
use Symfony\Component\String\AbstractString;
use Symfony\Component\String\UnicodeString;
use TRegx\SafeRegex\Exception\PregException;
use TRegx\SafeRegex\preg;
use UnexpectedValueException;

class StringBuffer
{
    private AbstractString $buffer;

    public function __construct(string $buffer)
    {
        $this->buffer = new UnicodeString($buffer);
    }

    public function readUntil(string $terminator, bool $trimWhitespaceAfterwards = true): string
    {
        return $this->readUntilRegexp(preg::quote($terminator), $trimWhitespaceAfterwards);
    }

    public function readUntilEolOrEof(): string
    {
        return $this->readUntilRegexp("\n|$");
    }

    public function readUntilWhitespace(): string
    {
        return $this->readUntilRegexp("\s");
    }

    public function readToken(): string
    {
        $terminator = $this->readCharacter();

        return $this->readUntil($terminator);
    }

    public function skipWhitespace(): void
    {
        $this->buffer = $this->buffer->trimStart();
    }

    public function isEmpty(): bool
    {
        return 0 === $this->buffer->length();
    }

    private function readUntilRegexp(string $terminator, bool $trimWhitespaceAfterwards = true): string
    {
        try {
            $parts = preg::split(pattern($terminator)->delimited(), $this->buffer->toString(), 2);
        } catch (PregException $e) {
            throw new RuntimeException("Terminator '$terminator' is not a valid regexp: {$e->getMessage()}");
        }

        if (count($parts) < 2) {
            throw new UnexpectedValueException("Unable to find '{$terminator}' in the remaining buffer '{$this->buffer}'");
        }

        $this->buffer = new UnicodeString($parts[1]);

        if ($trimWhitespaceAfterwards) {
            $this->skipWhitespace();
        }

        return $parts[0];
    }

    public function readCharacter(): string
    {
        $result = $this->buffer->slice(0, 1)->toString();
        $this->buffer = $this->buffer->slice(1);

        return $result;
    }
}
