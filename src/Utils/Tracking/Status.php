<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Traits\UtilityClass;

final class Status
{
    use UtilityClass;

    public const OPEN = true;
    public const CLOSED = false;
    public const UNKNOWN = null;

    public static function text(?bool $status): string
    {
        if (Status::OPEN === $status) {
            return 'OPEN';
        } elseif (Status::CLOSED === $status) {
            return 'CLOSED';
        } else {
            return 'UNKNOWN';
        }
    }
}
