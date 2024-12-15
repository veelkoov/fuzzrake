<?php

declare(strict_types=1);

namespace App\ValueObject\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async-msg-queue')]
readonly class EmailNotificationV1
{
    public function __construct(
        public string $subject,
        public string $contents,
        public string $recipient = '',
        public string $attachedJsonData = '',
    ) {
    }
}
