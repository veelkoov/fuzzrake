<?php

declare(strict_types=1);

namespace App\ValueObject;

class Notification
{
    public function __construct(
        public readonly string $subject,
        public readonly string $contents,
    ) {
    }
}
