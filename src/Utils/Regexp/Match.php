<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

class Match
{
    private $regexp;
    private $variant;
    private $match;
    private $matchInContext;

    public function __construct(Regexp $regexp, Variant $variant, string $match, string $matchInContext)
    {
        $this->regexp = $regexp;
        $this->variant = $variant;
        $this->match = $match;
        $this->matchInContext = $matchInContext;
    }

    public function getRegexp(): Regexp
    {
        return $this->regexp;
    }

    public function getVariant(): Variant
    {
        return $this->variant;
    }

    public function getMatch(): string
    {
        return $this->match;
    }

    public function getMatchInContext(): string
    {
        return $this->matchInContext;
    }
}
