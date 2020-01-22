<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\TrackingRegexp;
use App\Utils\Regexp\Variant;
use App\Utils\StrContext\StrContextInterface;

class Match implements MatchInterface
{
    private TrackingRegexp $regexp;
    private Variant $variant;
    private StrContextInterface $strContext;

    public function __construct(TrackingRegexp $regexp, Variant $variant, StrContextInterface $match)
    {
        $this->regexp = $regexp;
        $this->variant = $variant;
        $this->strContext = $match;
    }

    public function getRegexp(): TrackingRegexp
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
