<?php

declare(strict_types=1);

namespace App\Utils\Notifications;

class Notification
{
    public function __construct(
        public readonly string $subject,
        public readonly string $contents,
    ) {
    }
}
