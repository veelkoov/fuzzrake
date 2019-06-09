<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

class Status
{
    const OPEN = true;
    const CLOSED = false;
    const UNKNOWN = null;

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
