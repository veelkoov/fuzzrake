<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;
use Symfony\Component\String\AbstractString;
use Symfony\Component\String\UnicodeString;
use TRegx\CleanRegex\Pattern;
use TRegx\Exception\MalformedPatternException;
use TRegx\SafeRegex\preg;
use UnexpectedValueException;

class StringBuffer
{
    private AbstractString $buffer;

    /**
     * @var array<string, Pattern>
     */
    private array $terminatorPatternCache = [];

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

    public function readUntilWhitespaceOrEof(): string
    {
        return $this->readUntilRegexp('\s|$');
    }

    public function readUntilWhitespace(): string
    {
        return $this->readUntilRegexp('\s');
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

    public function readUntilRegexp(string $terminator, bool $trimWhitespaceAfterwards = true): string
    {
        try {
            $pattern = $this->terminatorPatternCache[$terminator] ??= pattern($terminator);

            $parts = $pattern->splitStart($this->buffer->toString(), 1);
        } catch (MalformedPatternException $e) {
            throw new RuntimeException("Terminator '$terminator' is not a valid regexp: {$e->getMessage()}");
        }

        if (count($parts) < 2) {
            throw new UnexpectedValueException("Unable to find '$terminator' in the remaining buffer '$this->buffer'");
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

    public function peekAll(): string
    {
        return $this->buffer->toString();
    }
}
