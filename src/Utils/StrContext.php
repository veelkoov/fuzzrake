<?php

declare(strict_types=1);

namespace App\Utils;

class StrContext
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

    public static function createFrom(string $input, string $match, int $contextLength): StrContext
    {
        $index = strpos($input, $match);
        $beforeIndex = max(0, $index - $contextLength);

        return new self(
            substr($input, $beforeIndex, 0 === $beforeIndex ? $index : $contextLength),
            $match,
            substr($input, $index + strlen($match), $contextLength));
    }
}
