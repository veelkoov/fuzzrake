<?php

declare(strict_types=1);

namespace App\ValueObject;

readonly class Notification
{
    public function __construct(
        public string $subject,
        public string $contents,
        public string $recipient = '',
        public string $attachedJsonData = '',
    ) {
    }
}
