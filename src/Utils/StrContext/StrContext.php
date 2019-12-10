<?php

declare(strict_types=1);

namespace App\Utils\StrContext;

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

    public function empty(): bool
    {
        return false;
    }
}
