<?php

declare(strict_types=1);

namespace App\ValueObject\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async-msg-queue')]
final readonly class SpeciesSyncNotificationV1
{
}
