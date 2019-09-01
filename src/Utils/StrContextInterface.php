<?php

namespace App\Utils;

interface StrContextInterface
{
    public function getBefore(): string;

    public function getSubject(): string;

    public function getAfter(): string;

    public function empty(): bool;
}
