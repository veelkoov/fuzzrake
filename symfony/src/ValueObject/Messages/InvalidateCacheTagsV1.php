<?php

declare(strict_types=1);

namespace App\ValueObject\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Veelkoov\Debris\Lists\StringList;

#[AsMessage('async-msg-queue')]
final readonly class InvalidateCacheTagsV1
{
    public StringList $tags;

    public function __construct(
        string ...$tags,
    ) {
        $this->tags = new StringList($tags)->freeze();
    }
}
