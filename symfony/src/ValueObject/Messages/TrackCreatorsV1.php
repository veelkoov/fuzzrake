<?php

declare(strict_types=1);

namespace App\ValueObject\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async-msg-queue')]
final readonly class TrackCreatorsV1
{
    /**
     * @param list<int> $idsOfCreators
     */
    public function __construct(public array $idsOfCreators)
    {
    }
}
