<?php

declare(strict_types=1);

namespace App\Utils\StrContext;

interface StrContextInterface
{
    public function getBefore(): string;

    public function getSubject(): string;

    public function getAfter(): string;

    public function empty(): bool;
}
