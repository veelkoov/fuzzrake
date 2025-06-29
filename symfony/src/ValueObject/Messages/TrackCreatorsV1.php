<?php

declare(strict_types=1);

namespace App\ValueObject\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Veelkoov\Debris\IntList;

#[AsMessage('async-msg-queue')]
final readonly class TrackCreatorsV1
{
    public function __construct(
        public IntList $idsOfCreators,
        public int $retryNumber = 0,
    ) {
    }
}
