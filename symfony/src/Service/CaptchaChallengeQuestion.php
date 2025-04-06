<?php

declare(strict_types=1);

namespace App\Service;

final readonly class CaptchaChallengeQuestion
{
    public function __construct(
        public string $question,
        public CaptchaChallengeAnswerSet $answers,
    ) {}
}
