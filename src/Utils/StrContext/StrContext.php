<?php

declare(strict_types=1);

namespace App\Utils\StrContext;

class StrContext implements StrContextInterface
{
    public function __construct(
        private string $before,
        private string $subject,
        private string $after,
    ) {
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
