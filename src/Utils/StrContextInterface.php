<?php

namespace App\Utils;

interface StrContextInterface
{
    public const STR_REPRESENTATION_SEPARATOR = "\n";

    public function getBefore(): string;

    public function getSubject(): string;

    public function getAfter(): string;

    public function asString(): string;
}
