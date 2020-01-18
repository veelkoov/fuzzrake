<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\TrackingRegexp;
use App\Utils\Regexp\Variant;
use App\Utils\StrContext\StrContextInterface;

interface MatchInterface
{
    public function getRegexp(): TrackingRegexp;

    public function getVariant(): Variant;

    public function getStrContext(): StrContextInterface;

    public function matched(): bool;
}
