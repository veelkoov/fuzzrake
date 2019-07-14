<?php

declare(strict_types=1);

namespace App\Utils;

class StrContext implements StrContextInterface
{
    private $before;
    private $subject;
    private $after;

    public function __construct(string $before, string $subject, string $after)
    {
        $this->before = $before;
        $this->subject = $subject;
        $this->after = $after;
    }

    public function getBefore(): string
    {
        return $this->before;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getAfter(): string
    {
        return $this->after;
    }

    public function asString(): string
    {
        return $this->before.self::STR_REPRESENTATION_SEPARATOR.$this->subject.self::STR_REPRESENTATION_SEPARATOR.$this->after;
    }

    public static function createFrom(string $input, string $match, int $contextLength): StrContext
    {
        $index = mb_strpos($input, $match);
        $beforeIndex = max(0, $index - $contextLength);

        return new self(
            mb_substr($input, $beforeIndex, 0 === $beforeIndex ? $index : $contextLength),
            $match,
            mb_substr($input, $index + strlen($match), $contextLength));
    }
}
