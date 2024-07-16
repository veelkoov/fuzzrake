<?php

declare(strict_types=1);

namespace App\ValueObject;

final class CacheTags // grep-cache-tags
{
    public const ARTISANS = 'artisans';
    public const CODE = 'code'; // TODO: Unused since 05a9d7b45dea0d4cae11bd24da21d11bad2c369a. Remove.
    public const TRACKING = 'tracking';
}
