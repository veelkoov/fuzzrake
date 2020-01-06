<?php

declare(strict_types=1);

namespace App\Utils\StrContext;

use App\Utils\NullObjectTrait;

class NullStrContext implements StrContextInterface
{
    use NullObjectTrait;

    public function getBefore(): string
    {
        return '';
    }

    public function getSubject(): string
    {
        return '';
    }

    public function getAfter(): string
    {
        return '';
    }

    public function empty(): bool
    {
        return true;
    }
}
