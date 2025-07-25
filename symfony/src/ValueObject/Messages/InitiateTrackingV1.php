<?php

declare(strict_types=1);

namespace App\ValueObject\Messages;

use App\Tracking\TrackCreatorsTask;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async-msg-queue')]
final readonly class InitiateTrackingV1
{
    public function __construct(
        public int $retriesLimit = TrackCreatorsTask::MAX_RETRIES,
        public bool $refetchPages = true,
    ) {
    }
}
