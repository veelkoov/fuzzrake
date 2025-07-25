<?php

declare(strict_types=1);

namespace App\ValueObject\Messages;

use App\Tracking\TrackCreatorsTask;
use Symfony\Component\Messenger\Attribute\AsMessage;
use Veelkoov\Debris\IntList;

#[AsMessage('async-msg-queue')]
final readonly class TrackCreatorsV1
{
    public function __construct(
        public IntList $idsOfCreators,
        public int $retriesLimit = TrackCreatorsTask::MAX_RETRIES,
        public bool $refetchPages = true,
    ) {
        $this->idsOfCreators->freeze();
    }

    public function retryAllowed(): bool
    {
        return $this->retriesLimit > 0;
    }
}
