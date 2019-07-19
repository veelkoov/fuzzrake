<?php

namespace App\Utils\Tracking;

use App\Utils\Regexp\Regexp;
use App\Utils\Regexp\Variant;
use App\Utils\StrContextInterface;

interface MatchInterface
{
    public function getRegexp(): Regexp;

    public function getVariant(): Variant;

    public function getStrContext(): StrContextInterface;

    public function matched(): bool;
}
