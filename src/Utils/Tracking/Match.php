<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\Regexp;
use App\Utils\Regexp\Variant;
use App\Utils\StrContextInterface;

class Match implements MatchInterface
{
    private Regexp $regexp;
    private Variant $variant;
    private StrContextInterface $strContext;

    public function __construct(Regexp $regexp, Variant $variant, StrContextInterface $match)
    {
        $this->regexp = $regexp;
        $this->variant = $variant;
        $this->strContext = $match;
    }

    public function getRegexp(): Regexp
    {
        return $this->regexp;
    }

    public function getVariant(): Variant
    {
        return $this->variant;
    }

    public function getStrContext(): StrContextInterface
    {
        return $this->strContext;
    }

    public function matched(): bool
    {
        return true;
    }
}
