<?php

declare(strict_types=1);

namespace App\Service;

final readonly class CaptchaChallengeAnswer
{
    public function __construct(
        public string $description,
        public string $emoticon,
        public bool $correct,
    ) {}
}
