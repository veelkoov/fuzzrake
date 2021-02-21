<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\TrackingRegexp;
use App\Utils\Regexp\Variant;
use App\Utils\StrContext\StrContextInterface;

class TrackingMatch implements MatchInterface
{
    public function __construct(
        private TrackingRegexp $regexp,
        private Variant $variant,
        private StrContextInterface $strContext,
    ) {
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
