<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use Override;

class GenericStrategy extends Strategy
{
    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return true;
    }
}
