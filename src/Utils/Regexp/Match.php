<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\StrContext;

class Match
{
    private $regexp;
    private $variant;
    private $match;

    public function __construct(Regexp $regexp, Variant $variant, StrContext $match)
    {
        $this->regexp = $regexp;
        $this->variant = $variant;
        $this->match = $match;
    }

    public function getRegexp(): Regexp
    {
        return $this->regexp;
    }

    public function getVariant(): Variant
    {
        return $this->variant;
    }

    public function getMatch(): StrContext
    {
        return $this->match;
    }
}
