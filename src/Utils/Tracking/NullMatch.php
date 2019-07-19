<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\NullObjectTrait;
use App\Utils\NullStrContext;
use App\Utils\Regexp\Regexp;
use App\Utils\Regexp\Variant;
use App\Utils\StrContextInterface;

class NullMatch implements MatchInterface
{
    use NullObjectTrait;

    public function getRegexp(): Regexp
    {
        throw self::incomplete();
    }

    public function getVariant(): Variant
    {
        throw self::incomplete();
    }

    public function getStrContext(): StrContextInterface
    {
        return NullStrContext::get();
    }

    public function matched(): bool
    {
        return false;
    }
}
